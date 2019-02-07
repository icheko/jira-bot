<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedAdminUser();
    }

    public function seedAdminUser()
    {
        $adminName = "Jose Pacheco";
        $user = User::where('name', '=', $adminName)->first();
        if (! $user) {
            $user = factory(User::class)->create([
                'name' => $adminName,
                'password'   => bcrypt(123),
                'email'      => 'icheko@gmail.com',
            ]);
        }
        $user->password = bcrypt(123);
        $user->save();

        return $user;
    }
}
