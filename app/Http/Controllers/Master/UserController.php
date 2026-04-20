<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function fetchAllRows()
    {

        $rows = User::with('department')->select('id', 'name', 'email', 'phone', 'role', 'last_login', 'department_id', 'company_id', 'created_at');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);
                },
                'class' => 'row-item',
            ])
            ->editColumn('role', function ($model) {
                return roleDisplay($model->role);
            })
            ->editColumn('created_at', function ($model) {
                return Carbon::parse($model->created_at)->format('d-m-Y');
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = new User();
        $departments = Department::all()->pluck('name', 'id');
        return view('modules.master.user.user-form', compact('user', 'departments'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $departments = Department::all()->pluck('name', 'id');
        return view('modules.master.user.user-form', compact('user', 'departments'));
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|min:10|max:15',
            'department' => 'nullable|int',

            'login_permission' => 'required',
            'password' => 'nullable|string|min:8|max:50',
            'confirm_password' => 'nullable|string|same:password',

            'role' => 'required|string|min:0',
            'status' => 'required|string|min:0',
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $user = User::findOrFail($request->input('data-id'));
        } else {
            $user = new User();
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->department_id = $request->input('department');
        $user->login_permission = $request->input('login_permission');

        // Only update password if it's provided
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->status = $request->input('status');
        $user->role = $request->input('role');
        $user->address_1 = $request->input('address1');
        $user->address_2 = $request->input('address2');
        $user->state = $request->input('state');
        $user->city = $request->input('city');
        $user->postal_code = $request->input('postal_code');
        $user->country = $request->input('country');
        $user->alternate_email = $request->input('alt_email');
        $user->remark = $request->input('remark');
        $this->setBaseColumns($user);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'module_id' => $user->id,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('modules.settings.account', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'full_name' => ['required', 'string', 'min:4', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Max 2MB

            // Password fields are only required if the user attempts to change their password
            'current_password' => [
                'nullable',
                // Only validate current_password if a new password is provided
                Rule::requiredIf($request->filled('new_password')),
                'string',
            ],
            'new_password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed', // Requires 'confirm_new_password' field to match
            ],
            // 'confirm_new_password' is automatically handled by the 'confirmed' rule on new_password
        ];

        $validatedData = $request->validate($rules);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided current password does not match our records.'])
                    ->withInput();
            }
        }

        $updateData = [
            'name' => $validatedData['full_name'],
            'phone' => $validatedData['phone'],
        ];

        // Handle password change if 'new_password' is provided and validation passed
        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($validatedData['new_password']);
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Define the path relative to the disk root
            $relativeStoragePath = 'profile-photos/' . $user->company_id . '/' . $user->id;

            try {
                // Laravel's storeAs throws an exception on failure, so we rely on that.
                $path = $file->storeAs($relativeStoragePath, $fileName, 'public');

                // This block only runs if $path was successfully assigned (i.e., the file was saved)
                if ($path) {
                    // Delete the old photo only if the new one was successfully saved
                    if ($user->profile_photo_path) {
                        // If the path already has 'storage/' prefix, remove it for deletion
                        $oldPath = $user->profile_photo_path;
                        if (strpos($oldPath, 'storage/') === 0) {
                            $oldPath = substr($oldPath, 8); // Remove 'storage/' prefix
                        }
                        Storage::disk('public')->delete($oldPath);
                    }

                    // Store the path in a consistent format - without 'storage/' prefix
                    // The 'storage/' prefix should be added only when displaying the image in views
                    $updateData['profile_photo_path'] = $path;
                }

            } catch (\Exception $e) {
                // Log the actual exception message from the server/filesystem
                \Log::error("File upload failed for user ID {$user->id}: " . $e->getMessage());

                // Return a user-friendly error response
                return response()->json([
                    'status' => 'error',
                    'message' => 'File upload failed: ' . $e->getMessage(),
                ], 500);
            }
        }
// Continue with the rest of your update logic...
        $user->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'module_id' => $user->id,
        ]);
    }

    public function actions($id)
    {
        $user = User::select('id', 'status')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $user->id,
            'type' => 'item',
            'icon' => 'view'
        ], [
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $user->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $user->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);

        return response()->json($contextMenu->values());
    }

    public function overview($id)
    {
        $user = User::with('department')->findOrFail($id);
        return view('modules.master.user.view-overview', compact('user'));
    }
}
