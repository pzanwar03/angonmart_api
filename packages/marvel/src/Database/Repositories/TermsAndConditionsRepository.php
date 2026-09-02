<?php


namespace Marvel\Database\Repositories;

use Exception;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Illuminate\Http\Request;
use Marvel\Database\Models\TermsAndConditions;

class TermsAndConditionsRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title' => 'like',
        'language',
        'type',
        'issued_by',
        'is_approved'
    ];

    /**
     * @var array
     */
    protected $dataArray = [
        'title',
        'description',
        'language',
        'slug',
    ];


    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TermsAndConditions::class;
    }



    /**
     * storeTermsAndConditions
     *
     * @param  mixed $request
     * @return void
     */
    public function storeTermsAndConditions($request)
    {
        try {
            $termsAndConditions                = [];
            $termsAndConditions['title']       = $request['title'];
            $termsAndConditions['description'] = $request['description'];
            $termsAndConditions['user_id']     = $request->user()->id;
            $termsAndConditions['type']        = 'global';
            $termsAndConditions['issued_by']   = 'Super Admin';
            $termsAndConditions['language']    = $request['language'] ?? DEFAULT_LANGUAGE;
            $termsAndConditions['is_approved'] = true;

            $this->create($termsAndConditions);
            return $termsAndConditions;
        } catch (Exception $th) {
            throw new Exception(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }


    /**
     * updateTermsAndConditions
     *
     * @param  mixed $request
     * @param  mixed $termsAndConditions
     * @return void
     */
    public function updateTermsAndConditions(Request $request, TermsAndConditions $termsAndConditions)
    {
        try {
            $termsAndConditions->update($request->only($this->dataArray));
            return $termsAndConditions;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }
}
