<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthenticationController extends Controller
{
    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string|unique:users,name',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'is_admin' => 'required'
        ]);

        $user = User::create([
            'name' => strtolower($fields['name']),
            'email' => strtolower($fields['email']),
            'password' => bcrypt($fields['password']),
        ]);

        if ($user) {
            User::where('email', $fields['email'])->update(['is_admin' => $fields['is_admin']]);
        }

        $token = $user->createToken('przejazdykmtoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        $tabname = "data_".strtolower($fields['name']);
        Schema::create($tabname, function ($table) {
            $table->increments('id');
            $table->string('tabor');
            $table->string('line');
            $table->string('direction');
            $table->string('first');
            $table->string('last');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        return response($response, 201);
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('przejazdykmtoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    public function changePassword(Request $request) {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed',
            'email' => 'required|string'
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        $newPassword = bcrypt($request->input('password'));
        $oldPassword = $request->input('old_password');
        $oldPasswordInDB = $user->password;
        $hashCheck = Hash::check($oldPassword, $oldPasswordInDB);

        if ($hashCheck) {
            User::where('email', $email)
                ->update(['password' => $newPassword]);
            return response(['message' => 'Password was changed']);
        }

        return response('', 400);
    }

    public function changeHints(Request $request) {
        $request->validate([
            'is_hint' => 'required',
            'email' => 'required|string'
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        $hint = $request->input('is_hint');

        if ($user) {
            User::where('email', $email)
                ->update(['is_hint' => $hint]);
            return response(['message' => 'Option was changed']);
        }

        return response('', 400);
    }

    public function usersList() {
        return User::all();
    }

    public function delete($id) {
        $user = User::where('id', $id)->first();
        $tabname = 'data_'.$user->name;
        Schema::dropIfExists($tabname);
        User::destroy($id);
    }
}
