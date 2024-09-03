<?php

namespace Database\Factories;

use App\Models\DayOfWeek;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $subjects = Subject::all()->pluck('id');
        $teachers = Teacher::all()->pluck('id');
        
        $schedules = Schedule::all()->pluck('id')->toArray();
        $rooms = Room::all()->pluck("id")->toArray();

        static $index = 0;

        $pair = array_map(null,$schedules,$rooms);

        $pair = $pair[$index++ % count($pair)];
        $dates = [Carbon::today(),Carbon::today()->addMonth(),Carbon::today()->addMonths(2)];
        $date = fake()->randomElement($dates);
        return [
            "schedule_id" => $pair[0],
            'subject_id' => fake()->randomElement($subjects->toArray()),
            'teacher_id' => fake()->randomElement($teachers->toArray()),
            'room_id' => $pair[1],
            'start_at' => $date,
            'end_at' =>  $date->addMonth(),
            'minimum_students' => fake()->numberBetween(14,18),
            'status' => fake()->randomElement(["P","C","O"]),
            'salary_type' => fake()->randomElement(["C","S"]),
            'salary_amount' => fake()->randomElement([3000,5000,4600]),
            'cost' => fake()->randomElement(["200","300"]),
            'certificate_cost' => fake()->randomElement(["2000","3000"]),
        ];
    }
}
