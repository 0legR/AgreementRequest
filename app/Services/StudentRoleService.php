<?php
namespace App\Services;

use App\Models\user\Student;
use App\Models\StudentInfo;
use App\User;
use Carbon\Carbon;

/**
 * manage user with role student 
 */
class StudentRoleService
{
    protected $user;
    protected $organization_id;

    function __construct(User $user, $organization_id = null)
    {
        $this->user = $user;
        $this->organization_id = $organization_id ?: session()->get('organization');
    }

    /**
     * Checks is user already assigned to StudentInfo model
     */
    private function checkoutIsStudentInfo()
    {
        return StudentInfo::find($this->user->id);
    }

    /**
     * Checks is user already assigned to Student model
     */
    private function checkoutIsStudent()
    {
        return Student::active()->byJobOrganization($this->organization_id)->find($this->user->id);
    }

    /**
     * Checks is user already assigned to Student and StudentInfo models
     */
    public function createIfNotExists()
    {
        $studentInfo = $this->checkoutIsStudentInfo();
        $student = $this->checkoutIsStudent();
        if($studentInfo && $student) {
            return;
        } else {
            if (!$student) {
                $this->setStudentRole();
            }
            if (!$studentInfo) {
                $this->setStudentInfo();
            }
        }
    }

    /**
     * sets new note to user_student table
     */
    public function setStudentRole()
    {
        Student::create([
            'id_user' => $this->user->id,
            'start_date' => Carbon::now(),
            'assigned_by' => \Auth::user()->id,
            'id_organization' => $this->organization_id
        ]);
    }

    /**
     * sets new note to student_info table
     */
    public function setStudentInfo()
    {
        StudentInfo::create([
            'id_student' => $this->user->id,
            'firstName' => $this->user->firstName,
            'secondName' => $this->user->secondName,
            'middle_name' => $this->user->middleName,
            'birthday' => $this->user->birthday,
            'mobile_phone' => $this->user->phone,
            'email' => $this->user->email,
            'address' => $this->user->address,
            'facebook' => $this->user->facebook,
            'education' => $this->user->education,
            'source_about_us' => $this->user->aboutUs,
            'interests' => $this->user->interests,
            'current_job' => $this->user->current_job,
            'prev_job' => $this->user->prev_job,
            'rather_form_study' => $this->user->educform,
            'rather_form_payment' => $this->user->education_shift,
            'id_organization' => $this->organization_id
        ]);
    }
}