<?php

namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\TermsAndConditions;
use Marvel\Database\Repositories\TermsAndConditionsRepository;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CreateTermsAndConditionsRequest;
use Marvel\Http\Requests\UpdateTermsAndConditionsRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Marvel\Http\Resources\TermsConditionResource;

class TermsAndConditionsController extends CoreController
{
    public $repository;

    public function __construct(TermsAndConditionsRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|TermsAndConditions[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        // $language = $request->language ?? DEFAULT_LANGUAGE;
        $termsAndConditions = $this->fetchTermsAndConditions($request)->paginate($limit)->withQueryString();
        $data = TermsConditionResource::collection($termsAndConditions)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    public function fetchTermsAndConditions(Request $request)
    {

        try {
            $user = $request->user();
            $language = $request->language ?? DEFAULT_LANGUAGE;

            // authorized users (super_admin/staff) can see all terms, including unapproved ones
            // guests / customers only see approved ones
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STAFF))) {
                return $this->repository->where('language', $language);
            }

            return $this->repository->where('is_approved', '=', true)->where('language', $language);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

    /**
     * Store a newly created termsAndConditions in storage.
     *
     * @param CreateTermsAndConditionsRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(CreateTermsAndConditionsRequest $request)
    {
        try {
            return $this->repository->storeTermsAndConditions($request);
            // return $this->repository->create($validatedData);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_CREATE_THE_RESOURCE, $e->getMessage());
        }
    }

    /**
     * Display the specified termsAndConditions.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $slug)
    {
        try {
            $language = $request->language ?? DEFAULT_LANGUAGE;
            $termsAndCondition = $this->repository->where('language', $language)->where('slug', '=', $slug)->first();
            return new TermsConditionResource($termsAndCondition);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND, $e->getMessage());
        }
    }

    /**
     * Update the specified terms and conditions
     *
     * @param UpdateTermsAndConditionsRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateTermsAndConditionsRequest $request, $id)
    {
        try {
            $request["id"] = $id;
            return $this->updateTermsAndConditions($request);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_UPDATE_THE_RESOURCE, $e->getMessage());
        }
    }

    /**
     * updateTermsAndConditions
     *
     * @param  UpdateTermsAndConditionsRequest $request
     * @return void
     */
    public function updateTermsAndConditions(UpdateTermsAndConditionsRequest $request)
    {
        $termsAndConditions = $this->repository->findOrFail($request['id']);
        return $this->repository->updateTermsAndConditions($request, $termsAndConditions);
    }

    /**
     * Remove the specified terms and conditions
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $request->merge(['id' => $id]);
        return $this->deleteTermsConditions($request);
    }

    public function deleteTermsConditions(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STAFF))) {
                return $this->repository->findOrFail($request->id)->delete();
            }
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND, $e->getMessage());
        }
    }
}
