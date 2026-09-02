<?php


namespace Marvel\Database\Repositories;

use Exception;
use Marvel\Database\Models\Faqs;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Illuminate\Http\Request;

class FaqsRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'faq_title' => 'like',
        'language',
        'faq_type',
        'issued_by'
    ];

    /**
     * @var array
     */
    protected $dataArray = [
        'faq_title',
        'faq_description',
        'language',
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
        return Faqs::class;
    }



    /**
     * storeFaqs
     *
     * @param  mixed $request
     * @return void
     */
    public function storeFaqs($request)
    {
        try {
            $faqs                    = [];
            $faqs['faq_title']       = $request['faq_title'];
            $faqs['faq_description'] = $request['faq_description'];
            $faqs['user_id']         = $request->user()->id;
            $faqs['faq_type']        = 'global';
            $faqs['issued_by']       = 'Super Admin';
            $faqs['language']        = $request['language'] ?? DEFAULT_LANGUAGE;

            $this->create($faqs);
            return $faqs;
        } catch (Exception $th) {
            throw new Exception(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }


    /**
     * updateFaqs
     *
     * @param  mixed $request
     * @param  mixed $faqs
     * @return void
     */
    public function updateFaqs(Request $request, Faqs $faqs)
    {
        try {
            $faqs->update($request->only($this->dataArray));
            return $faqs;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }
}
