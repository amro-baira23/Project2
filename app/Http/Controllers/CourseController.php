<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\CourseTime;
use App\Models\DayOfWeek;
use App\Models\Schedule;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use App\Http\Controllers\Controller;
use App\Http\Resources\CurrentCoursesResource;
use App\Http\Resources\StudentCourseCollection;
use App\Http\Resources\StudentCourseResource;
use App\Models\Student;
use Illuminate\Validation\Rule ;

class CourseController extends Controller
{
    //Add Course Function
    public function addCourse(CourseRequest $request)
    {
        $schedule = Schedule::create([
            'start' => $request->start,
            'end' => $request->end,
        ]);

        $days = explode(',', $request->days);

        $courses = Course::get();

        // foreach ($courses as $course) {
        //     foreach ($course->schedule->days as $course_day) {
        //         foreach ($days as $day) {
        //             if ($day == $course_day->day && $request->start >= $course->schedule->time->start && $request->start < $course->schedule->time->end && $request->start_at >= $course->start_at && $request->start_at <= $course->end_at && ($course->room_id == $request->room_id || $course->teacher_id == $request->teacher_id)) {
        //                 return error('You cannot set course time in this days', 'You cannot set course time in this days', 502);
        //             }
        //         }
        //     }
        // }

        // CourseTime::create([
        //     'schedule_id' => $schedule->id,
            // 'start' => $request->start,
            // 'end' => $request->end,
        // ]);

        foreach ($days as $day)
            DayOfWeek::create([
                'schedule_id' => $schedule->id,
                'day' => $day,
            ]);

        Course::create([
            'subject_id' => $request->subject_id,
            'schedule_id' => $schedule->id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'minimum_students' => $request->minimum_students,
            'salary_type' => $request->salary_type,
            'salary_amount' => $request->salary_amount,
            'cost' => $request->cost,
            'status' => $request->status,
        ]);

        return success(null, 'this course added successfully', 201);
    }

   
    //Edit Course Function
    public function editCourse(Course $course, CourseRequest $request)
    {
        $schedule = $course->schedule;
        $schedule->update([
            'start' => $request->start,
            'end' => $request->end,
        ]);
        $days = explode(',', $request->days);

        $courses = Course::whereNot('id', $course->id)->get();

        foreach ($courses as $c) {
            foreach ($c->schedule->days as $course_day) {
                foreach ($days as $day) {
                    if ($day == $course_day->day && $request->start >= $c->schedule->time->start && $request->start < $c->schedule->time->end && $request->start_at >= $c->start_at && $request->start_at <= $c->end_at && ($c->room_id == $request->room_id || $c->teacher_id == $request->teacher_id)) {
                        return error('You cannot set course time in this days', 'You cannot set course time in this days', 502);
                    }
                }
            }
        }

        foreach ($course->schedule->days as $day)
            $day->delete();

        foreach ($days as $day)
            DayOfWeek::create([
                'schedule_id' => $course->schedule->id,
                'day' => $day,
            ]);

        $course->update([
            'subject_id' => $request->subject_id,
            'schedule_id' => $schedule->id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'minimum_students' => $request->minimum_students,
            'salary_type' => $request->salary_type,
            'salary_amount' => $request->salary_amount,
            'cost' => $request->cost,
            'status' => $request->status,
        ]);

        return success(null, 'this course updated successfully');
    }

    //Get Courses Function
    public function getCourses()
    {
        $courses = Course::with('subject', 'schedule.days', 'teacher', 'room')->whereNot("status","C")
        ->where("end_at",">",today())
        ->get();
        return success(CurrentCoursesResource::collection($courses), null);
    }

    //Get Course Information Function
    public function getCourseInformation(Course $course)
    {
        return success(new CurrentCoursesResource($course), null);
    }

    //Delete Course Function
    public function deleteCourse(Course $course)
    {
        $course->delete();

        return success(null, 'this course deleted successfully');
    }

    public function getStudents(Course $course){
        return ($course->students);
    }

    public function addStudent(Course $course,Request $request){
        $request->validate([
            "student" => ["required",Rule::exists("students","id")->withoutTrashed()],
            "with_certificate" => ["required","bool"]
        ]);
        $student = Student::find($request->student);
        $course->students()->syncWithoutDetaching([
            "student_id" => $request->student,
            "with_diploma" => $request->with_certificate
        ]);
        return success(null,  "student been enrolled successfuly");
    }

    public function editStudent(Course $course,Student $student,Request $request){
        $request->validate([
            "with_certificate" => ["required","bool"]
        ]);

        return $course->students()->findOrFail($student->id)->pivot
                ->update(["with_diploma" => $request->with_certificate ]);
    }

    public function deleteStudent(Course $course,Student $student){
        $course->students()->detach($student);
        return success(null,null,204);
    }
    
}