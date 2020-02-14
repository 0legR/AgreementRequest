<?php

namespace App\Models;

use App\Http\Traits\Validation;
use App\Models\Title;

class EducationForm extends Title
{
    use Validation;

    protected $table = 'education_form';

    const ONLINE = 1;
    const OFFLINE = 2;
    const ONLINE_OFFLINE = 3;
    
    /**
     * @return array validation rules for model attributes.
     */
    private function rules($isUpdate = false)
    {
        $rules = [
            'title_ua' => 'string|regex:'.parent::UA_REGEX.'|required|max:50',
            'title_ru' => 'string|regex:'.parent::RU_REGEX.'|required|max:50',
            'title_en' => 'string|regex:'.parent::EN_REGEX.'|required|max:50'
        ];
        if ($isUpdate) {
            $rules['id'] = 'integer|required';
        }
        return $rules;
    }

    public function getCoefficient() {
        if ($this->id == self::OFFLINE) {
            return config('settings.coeffModuleOffline');
        }
        return 1;
    }
}
