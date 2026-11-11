<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('status') && in_array($request->query('status'), ['Pending', 'Active', 'Inactive'], true)) {
            $query->where('status', $request->query('status'));
        }

        return view('users.index', [
            'users' => $query->orderBy('status')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['Admin', 'Operator'])],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Pending'])],
        ]);

        DB::transaction(function () use ($user, $data): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($lockedUser->isAdmin() && ($data['role'] !== 'Admin' || $data['status'] !== 'Active')) {
                $activeAdmins = User::query()
                    ->where('role', 'Admin')
                    ->where('status', 'Active')
                    ->lockForUpdate()
                    ->count();

                if ($activeAdmins <= 1) {
                    throw ValidationException::withMessages([
                        'status' => 'The system must retain at least one active administrator.',
                    ]);
                }
            }

            $lockedUser->update($data);
        });

        return back()->with('success', 'Account access updated successfully.');
    }
}
