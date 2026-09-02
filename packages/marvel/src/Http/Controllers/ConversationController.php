<?php


namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Conversation;
use Marvel\Database\Repositories\ConversationRepository;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\ConversationCreateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class ConversationController extends CoreController
{
    public $repository;

    public function __construct(ConversationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Conversation[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        $conversation = $this->fetchConversations($request);

        return $conversation->paginate($limit);
    }

    public function show($conversation_id)
    {
        $user = Auth::user();
        $conversation = $this->repository->with(['user.profile'])->findOrFail($conversation_id);
        $isStaff = $user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STAFF);
        abort_unless($isStaff || $user->id === $conversation->user_id, 404, 'Unauthorized');

        return $conversation;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Query|Conversation[]
     */
    public function fetchConversations(Request $request)
    {
        return $this->repository->where(function ($query) {
            $user = Auth::user();
            $isStaff = $user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STAFF);
            if (!$isStaff) {
                $query->where('user_id', $user->id);
            }
            $query->orderBy('updated_at', 'desc');
        })->with(['user.profile']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ConversationCreateRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(ConversationCreateRequest $request)
    {
        $user = $request->user();
        if (empty($user)) {
            throw new MarvelException(NOT_AUTHORIZED);
        }

        return $this->repository->firstOrCreate([
            'user_id' => $user->id,
        ]);
    }
}
