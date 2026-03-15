<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * List all users with their assigned roles.
     */
    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    /**
     * Provide roles for the frontend selection dropdown.
     */
    public function getRoles()
    {
        return response()->json(Role::all());
    }

    /**
     * Create a new staff member and assign a role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'], // Ensure role_id is in User $fillable
        ]);

        return response()->json($user->load('role'), 201);
    }

    /**
     * Update user details or change their role.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user->load('role'));
    }

    /**
     * Remove a user from the system.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }
        $user->delete();
        return response()->json(null, 204);
    }
}