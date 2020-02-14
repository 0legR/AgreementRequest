<?php

namespace App\Models\user;

use App\User;
use App\Http\Traits\Validation;
use App\Models\user\UserRoles;

class Accountant extends Role
{
    use Validation;

    const ROLE = UserRoles::ACCOUNTANT;

    /**
     * @return string the associated database table name
     */
    protected $table = 'user_accountant';
    protected $fillable = ['id_user', 'start_date', 'end_date', 'assigned_by', 'cancelled_by', 'id_organization'];
    protected $primaryKey = 'id_user';
    protected $relations = ['users', 'cancelledBy', 'assignedBy', 'user', 'organization'];
    /**
     * @return array validation rules for model attributes.
     */
    
    private function rules()
    {
        return [
            'id_user' => 'integer|required',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'cancelled_by' => 'integer|nullable',
            'assigned_by' => 'integer|required',
            'id_organization' => 'integer|required'
        ];
    }

    /**
     * @return array relational rules.
     */
    public function organization()
    {
        return $this->hasOne('App\Models\Organization', 'id', 'id_organization');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id', 'id_user');
    }

    public function cancelledBy()
    {
        return $this->hasMany(User::class, 'id', 'cancelled_by');
    }

    public function assignedBy()
    {
        return $this->hasMany(User::class, 'id', 'assigned_by');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    
    /**
     * @param $organization Organization
     * @return string sql for check role accountant.
     */
    public function checkRoleSql($organization=null){
        $condition=$organization?' and ac.id_organization='.$organization:'';
        return 'select "accountant" from user_accountant ac where ac.id_user = ? and ac.end_date IS NULL'.$condition;
    }

    /**
     * @return string the role title (ua)
     */
    public function title(){
        return __('messages.978');
    }

    public function getErrorMessage(){
        return $this->errorMessage;
    }


    public function attributes(User $user, $organization=null)
    {
        return array();
    }

    public function scopeByEmptyEndDate($query, $id_user)
    {
        return $query->where('id_user', $id_user)->whereNull('end_date');
    }
}
