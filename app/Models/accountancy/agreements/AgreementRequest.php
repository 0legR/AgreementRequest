<?php

/**
 * This is the model class for table "acc_agreement_requests".
 *
 * The followings are the available columns in table 'acc_user_agreements':
 * @property integer $id
 * @property integer $user_id
 * @property integer $service_id
 * @property integer $scheme_id
 */

namespace App\Models\accountancy\agreements;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Validation;
use App\Builders\Traits\AccountancyBuilderTrait;

class AgreementRequest extends Model
{
    use Validation, AccountancyBuilderTrait;

    const STATUSES = [
        'new' => 1,
        'unread' => 2,
        'read' => 3,
        'pending' => 4,
        'cancelled' => 5,
        'generated' => 6
    ];

    protected $table =  'acc_agreement_requests';

    protected $fillable = [ 'user_id', 'service_id', 'scheme_id', 'template_id', 'status_mode' ];

    /**
     * @return array validation rules.
     */
    private function rules($isUpdate = false)
    {
        $rules = [
            'user_id' => 'integer|required',
            'service_id' => 'integer|required',
            'scheme_id' => 'integer|required',
            'template_id' => 'integer|nullable',
            'status_mode' => 'integer|nullable'
        ];
        if ($isUpdate) {
            $rules['id'] = 'integer|required';
        }
        return $rules;
    }

    /**
     * @return array relational rules.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function acc_service()
    {
        return $this->belongsTo('App\Models\accountancy\services\Service', 'service_id');
    }

    public function acc_template_schemas()
    {
        return $this->belongsTo('App\Models\accountancy\paymentScheme\TemplateSchemes', 'scheme_id');
    }

    public function acc_user_written_agreement()
    {
        return $this->belongsTo('App\Models\accountancy\agreements\UserWrittenAgreement', 'template_id');
    }
    
    /**
     * @return scopes
     */

    public function scopeByOrganization($query)
    {
        return $query->whereHas('acc_service.acc_course_service.courseModel', function($q) {
            $q->byOrganization();
        })->orWhereHas('acc_service.acc_module_service.moduleModel', function($q) {
            $q->byOrganization();
        });
    }

    public function scopeByStudent($query)
    {
        return $query->where('user_id', \Auth::user()->id);
    }

    /**
     * serve functions
     */

    public function createSubject()
    {
        return 'Запит на створення договору';
    }

    public function approveSubject()
    {
        return 'Запит на затвердження змін до договору';
    }

    public function generatedSubject()
    {
        return 'Договір згенеровано';
    }

    public function createMessage()
    {
        return "{$this->user->firstName} {$this->user->secondName} створив запит на договір за сервісом {$this->acc_service->description}.";
    }

    public function approveMessage()
    {
        $message = "Перегляньте внесені зміни до договру за сервісом {$this->acc_service->description}. Та натисніть кнопку 'Погоджено' (на сторінці за посиланням нижче) у разі згоди зі змінами. Якщо потрібно узгодити якісь дані відправте відповідь на дане повідомлення.";
        $messageData = [
            'receiver' => $this->user_id,
            'message_text' => $message,
            'path' =>  "cabinet/index#/student/finances/agreement/requests/show/{$this->id}",
            'subject' => $this->approveSubject()
        ];
        return view('emails.agreements.new_request_to_accountant')->with([
            'messageData' => $messageData
        ])->render();
    }

    public function generatedMessage()
    {
        $message = "Згенеровано договір за сервісом {$this->acc_service->description}.";
        $messageData = [
            'receiver' => $this->user_id,
            'message_text' => $message,
            'path' =>  "cabinet/index#/student/finances/agreements/show/{$this->acc_user_written_agreement->id_agreement}",
            'subject' => $this->generatedSubject()
        ];
        return view('emails.agreements.generated')->with(['userWrittenAgreement' => $this->acc_user_written_agreement])->render();
    }
}
