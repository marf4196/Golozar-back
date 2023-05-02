<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin;
use App\Models\DentalService;
use App\Models\Worker;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Tooth;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        try {
            $admin = new Admin();
            $admin->name = 'Ramed';
            $admin->email = 'ramed@ramed.com';
            $admin->password = Hash::make('123456');
            $admin->type = 'admin';
            $admin->save();
        } catch (\Exception $e) {
        }
        try {
            $doc = new Worker();
            $doc->firstname = 'Ali';
            $doc->lastname = 'Mokhtari';
            $doc->sex = 'male';
            $doc->phone = '9171878751';
            $doc->birthdate = '1998-01-01';
            $doc->password = Hash::make('123456');
            $doc->save();
        } catch (\Exception $e) {
        }
        try {
            TaskCategory::insert([['id' => 1, 'name' => 'ویزیت'], ['id' => 2, 'name' => 'دارو']]);
        } catch (\Exception $e) {
        }
        try {
            Medicine::insert([['id' => 1, 'name' => 'ژل ضد حساسیت آفرا', 'quantity' => 5], ['id' => 2, 'name' => 'ضدعفونی کننده دندان و لثه', 'quantity' => 10], ['id' => 3, 'name' => 'ژل ضدعفونی لثه', 'quantity' => 3]]);
            MedicineCategory::insert(['id' => 1, 'name' => 'حساسیت دندان'], ['id' => 2, 'name' => 'بهداشت لثه']);
            $medicine1 = Medicine::find(1);
            $medicine1->categories()->sync(1);

            $medicine2 = Medicine::find(2);
            $medicine2->categories()->sync(1);

            $medicine3 = Medicine::find(3);
            $medicine3->categories()->sync(2);

        } catch (\Exception $e) {
        }
        try {
            $user = new User();
            $user->firstname = 'Ramed';
            $user->lastname = 'Rafiei';
            $user->sex = 'male';
            $user->blood_type = 'o+';
            $user->phone = '9171878751';
            $user->birthdate = '1998-01-01';
            $user->password = Hash::make('123456');
            $user->save();
        } catch (\Exception $e) {
        }

        if (!DentalService::count()) {
            DentalService::insert([
                [
                    'title' => 'جرم گیری',
                    'description' => 'جرم گیری دنداد',
                    'cost' => '2700000',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'title' => 'عصب کشی',
                    'description' => 'عصب کشی دندان',
                    'cost' => '4700000',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'title' => 'روکش',
                    'description' => 'نصب روکش دندان',
                    'cost' => '4300000',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        $user = User::where('phone', '9171878751')->first();
        try {
            $task = new Task();
            $task->title = 'وقت ویزیت اسفند ماه';
            $task->description = 'وقت تعیین شده برای ویزیت شدن توسط دندانپزشک';
            $task->date = '2023-03-15 17:30:00';
            $task->task_category_id = 1;
            $task->user_id = $user->id;
            $task->worker_id=1;
            $task->save();

            $task2 = new Task();
            $task2->title = 'داروهای تجویز شده';
            $task2->description = 'جهت رفع حساسیت';
            $task2->date = '2023-03-15 17:30:00';
            $task2->task_category_id = 2;
            $task2->user_id = $user->id;
            $task2->worker_id = 1;
            $task2->save();

            $task2->medicine()->attach([1 => ['quantiy' => 3]]);
            $task2->medicine()->attach([2 => ['quantiy' => 5]]);

        } catch (\Exception $e) {
        }
        try {
            if (!Tooth::count()) {
                Tooth::insert([
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '5', 'created_at' => now(), 'updated_at' => now()],
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '7', 'created_at' => now(), 'updated_at' => now()],
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '4', 'created_at' => now(), 'updated_at' => now()],
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '12', 'created_at' => now(), 'updated_at' => now()],
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '19', 'created_at' => now(), 'updated_at' => now()],
                        ['user_id' => $user->id, 'worker_id' => 1, 'tooth_index' => '27', 'created_at' => now(), 'updated_at' => now()],
                    ]
                );
            }
        } catch (\Exception $e) {
        }
        try {
            $teeth = $user->teeth()->get();
            $services = DentalService::pluck('id')->toArray();
            foreach ($teeth as $tooth) {
                \DB::table('tooth_service_relationships')->insert([
                    [
                        'tooth_id' => $tooth->id,
                        'service_id' => $services[0],
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'tooth_id' => $tooth->id,
                        'service_id' => $services[rand(1, 2)],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }
        } catch (\Exception $e) {
        }


    }
}
