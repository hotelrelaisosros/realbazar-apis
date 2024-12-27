<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\CnicImage;
use App\Models\FollowUserShop;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use App\Http\Controllers\Api\NotiSend;
use App\Models\ReferralUser;
use App\Models\UnpaidRegisterUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Date;
use App\Jobs\RemoveToken;
use Laravel\Passport\Token;


class AuthController extends Controller
{

    public function wholesaler(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $search = $request->search;
        $wholesaler = User::with('role', 'cnic_image')->where('role_id', 4);
        $wholesaler_count = User::with('role')->where('role_id', 4);
        if (!empty($search)) {
            $wholesaler->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $wholesaler_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $wholesalers = $wholesaler->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $wholesaler_counts = $wholesaler_count->count();
        $total_wholesaler_count = User::with('role')->where('role_id', 4)->count();
        $block_wholesaler_count = User::with('role')->where('role_id', 4)->where('is_block', true)->count();
        $unblock_wholesaler_count = User::with('role')->where('role_id', 4)->where('is_block', false)->count();

        if (count($wholesalers)) return response()->json(
            [
                'status' => true,
                'Message' => 'Wholesalers found',
                'wholesalers' => $wholesalers ?? [],
                'wholesalersCount' => $wholesaler_counts ?? [],
                'totalWholesalersCount' => $total_wholesaler_count ?? [],
                'blockWholesalersCount' => $block_wholesaler_count ?? [],
                'unblockWholesalersCount' => $unblock_wholesaler_count ?? [],
            ],
            200
        );
        return response()->json(
            [
                'status' => false,
                'Message' => 'Wholesalers not found',
                'wholesalers' => $wholesalers ?? [],
                'wholesalersCount' => $wholesaler_counts ?? [],
                'totalWholesalersCount' => $total_wholesaler_count ?? [],
                'blockWholesalersCount' => $block_wholesaler_count ?? [],
                'unblockWholesalersCount' => $unblock_wholesaler_count ?? [],
            ]
        );
    }

    public function user(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $search = $request->search;
        $user = User::with('role')->where('role_id', 3);
        $user_count = User::with('role')->where('role_id', 3);
        if (!empty($search)) {
            $user->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $user_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $users = $user->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $user_counts = $user_count->count();
        $total_users_count = User::with('role')->where('role_id', 3)->count();
        $block_users_count = User::with('role')->where('role_id', 3)->where('is_block', true)->count();
        $unblock_users_count = User::with('role')->where('role_id', 3)->where('is_block', false)->count();

        if (count($users)) return response()->json(
            [
                'status' => true,
                'Message' => 'Users found',
                'users' => $users ?? [],
                'usersCount' => $user_counts ?? [],
                'totalUsersCount' => $total_users_count ?? [],
                'blockUsersCount' => $block_users_count ?? [],
                'unblockUsersCount' => $unblock_users_count ?? [],
            ],
            200
        );
        return response()->json(
            [
                'status' => false,
                'Message' => 'Users not found',
                'users' => $users ?? [],
                'usersCount' => $user_counts ?? [],
                'totalUsersCount' => $total_users_count ?? [],
                'blockUsersCount' => $block_users_count ?? [],
                'unblockUsersCount' => $unblock_users_count ?? [],
            ]
        );
    }

    public function retailer(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $search = $request->search;
        $retailer = User::with(['role', 'cnic_image'])->where('role_id', 5);
        $retailer_count = User::with('role')->where('role_id', 5);
        if (!empty($search)) {
            $retailer->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $retailer_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $retailers = $retailer->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $retailer_counts = $retailer_count->count();
        $total_retailers_count = User::with('role')->where('role_id', 5)->count();
        $block_retailers_count = User::with('role')->where('role_id', 5)->where('is_block', true)->count();
        $unblock_retailers_count = User::with('role')->where('role_id', 5)->where('is_block', false)->count();

        if (count($retailers)) return response()->json(
            [
                'status' => true,
                'Message' => 'Retailers found',
                'retailers' => $retailers ?? [],
                'retailersCount' => $retailer_counts ?? [],
                'totalRetailersCount' => $total_retailers_count ?? [],
                'blockRetailersCount' => $block_retailers_count ?? [],
                'unblockRetailersCount' => $unblock_retailers_count ?? [],
            ],
            200
        );
        return response()->json(
            [
                'status' => false,
                'Message' => 'Retailers not found',
                'retailers' => $retailers ?? [],
                'retailersCount' => $retailer_counts ?? [],
                'retailersCount' => $retailer_counts ?? [],
                'totalRetailersCount' => $total_retailers_count ?? [],
                'blockRetailersCount' => $block_retailers_count ?? [],
                'unblockRetailersCount' => $unblock_retailers_count ?? [],
            ]
        );
    }



    public function checkReferral(Request $request)
    {
        $referr_user = ReferralUser::where('referral_code', $request->referral_code)->first();
        if (empty($referr_user)) return response()->json(['status' => false, 'Message' => 'Referral Code not valid', 'price' => 100]);
        else return response()->json(['status' => true, 'Message' => 'Referral Code valid', 'price' => 50], 200);
    }

    public function signup(Request $request)
    {

        $rules = [
            'first_name' => 'required',
            'password' => 'required',
            'last_name' => 'required',

        ];
        $messages = [
            'required' => 'This :attribute Field is Required',
        ];
        $attributes = [
            'first_name' => 'First Name',
            'password' => 'Password',
            'last_name' => 'Last Name',

        ];


        $rules['emailphone'] = 'required|email|unique:users,email';
        $attributes['emailphone'] = 'Email';
        $valid = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = new User();
            $user->role_id = 3;
            $user->first_name =  $request->first_name;
            $user->last_name =  $request->last_name;

            $user->password = Hash::make($request->password);

            $user->email = $request->emailphone;
            $user->phone = $request->phone;
            $user->country = $request->country;
            $user->is_user_app = true;


            //

            $user->token = rand(100000, 999999);
            $email = $request->emailphone;
            $token = $user->token;

            //

            if (!$user->save()) throw new Error("User Not Added!");
            event(new Registered($user));

            $link = URL::temporarySignedRoute(
                'verifications.verify',
                now()->addMinutes(30),
                ['id' => $user->id, 'hash' => sha1($user->email)]
            );
            // echo $link;

            Mail::send('admin.mail.appRegister',  compact('email', 'token', 'link'), function ($message) use ($email) {
                $message->to($email);
                $message->subject('Welcome to ProsArt');
            });

            $client = User::where('id', $user->id)->first();
            RemoveToken::dispatch($user)->delay(now()->addMinutes(30));


            DB::commit();

            return response()->json(['status' => true, 'Message' => "User Successfully Added", 'user' => $client,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }


    public function verifyLinkEmail(Request $request, $id, $hash)
    {
        try {
            $user = User::findOrFail($id);

            if (sha1($user->email) !== $hash) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid verification link.',
                ], 400);
            }

            // Validate the signed URL (ensures the link wasn't tampered with).
            if (!URL::hasValidSignature($request)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired verification link.',
                ], 400);
            }

            // Check if the email is already verified.
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already verified.',
                ]);
            }

            // Mark the email as verified.
            $user->markEmailAsVerified();

            return response()->json([
                'status' => true,
                'message' => 'Email successfully verified.',
            ]);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resendVerificationLink(Request $request)
    {
        $send_code = $request["send_code_only"];

        $rules['emailphone'] = 'required|exists:users,email';
        $attributes['emailphone'] = 'Email';
        $messages = [
            'required' => 'This :attribute field is required.',
            'exists' => 'The :attribute does not exist in our records.',
        ];
        $valid = Validator::make($request->all(), $rules, $messages, $attributes);
        try {
            $user = User::where('email', $request->emailphone)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found with this email address.',
                ], 404);
            }


            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email is already verified.',
                ]);
            }
            $user->token = rand(100000, 999999);
            $email = $request->emailphone;
            $token = $user->token;

            //

            $user->save();
            // Generate a new signed URL
            if (empty($request["send_code_only"])) {
                $link = URL::temporarySignedRoute(
                    'verifications.verify',
                    now()->addMinutes(30),
                    ['id' => $user->id, 'hash' => sha1($user->email)]
                );
            } else {
                $link = "";
            }


            // Send the email
            Mail::send('admin.mail.appRegister', compact('email', 'token', 'link'), function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Resend Verification Link');
            });

            RemoveToken::dispatch($user)->delay(now()->addMinutes(30));

            return response()->json([
                'status' => true,
                'message' => 'Verification link has been resent to your email.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function login(Request $request)
    {
        $rules = [
            'password' => 'required',
            // 'role' => 'required',
        ];
        $messages = [
            'required' => 'This :attribute Field is Required',
        ];
        $attributes = [
            'password' => 'Password',
        ];
        if (is_numeric($request->get('emailphone'))) {
            $rules['emailphone'] = 'required|digits:11|exists:users,phone';
            $attributes['emailphone'] = 'Phone';
        } else {
            $rules['emailphone'] = 'required|email|exists:users,email';
            $attributes['emailphone'] = 'Email';
        }
        $valid = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        // $role = Role::where('name',$request->role)->first();
        if (auth()->attempt([
            'email' => $request->emailphone,
            'password' => $request->password,
            // 'is_block' => false,
        ])) {
            $user = auth()->user()->load('role');
            // if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
            //     // if ($user->is_active == true) {
            //         $token = $user->createToken('token')->accessToken;
            //         $user->device_token = request()->token;
            //         $user->save();
            //         return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            //     // } else {
            //     //     auth()->logout();
            //     //     return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
            //     // }
            // } else {
            if ($user->is_block == true) return response()->json(['status' => false, 'Message' => 'Your Account Status has been Blocked']);
            $token = $user->createToken('token')->accessToken;
            $user->device_token = request()->token;
            $user->save();
            return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            // }
        }

        // elseif (auth()->attempt([
        //     'phone' => $request->emailphone,
        //     'password' => $request->password,
        //     // 'is_block' => false,
        // ])) {
        //     $user = auth()->user()->load('role');
        //     // // if ($user->role->name == 'holeseller' || $user->role->name == 'retailer') {
        //     //     if ($user->is_active == true) {
        //     //         $token = $user->createToken('token')->accessToken;
        //     //         $user->device_token = request()->token;
        //     //         $user->save();
        //     //         return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
        //     //     } else {
        //     //         auth()->logout();
        //     //         return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
        //     //     }
        //     // } else {
        //     if ($user->is_block == true) return response()->json(['status' => false, 'Message' => 'Your Account Status has been Blocked']);
        //     $token = $user->createToken('token')->accessToken;
        //     $user->device_token = request()->token;
        //     $user->save();
        //     return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
        //     // }
        // }
        else {
            return response()->json(['status' => false, 'Message' => 'Invalid Credentials']);
        }
    }

    public function logout()
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!empty($user)) {
            $userTokens = $user->tokens;
            foreach ($userTokens as $token) {
                $token->revoke();
            }
            if ($user->save()) {
                // if (Auth::logout())
                return response()->json(['status' => true, 'Message' => 'Logout Successfully'], 200);
                // else return response()->json(['status' => false, 'Message' => 'Logout Failed']);
            } else return response()->json(['status' => false, 'Message' => 'Logout Failed']);
        } else return response()->json(['status' => false, 'Message' => 'User not Found']);
    }

    public function forgot(Request $request)
    {
        $rules = [];
        $messages = [
            'required' => 'This :attribute Field is Required',
        ];
        $attributes = [];

        $rules['emailphone'] = 'required|email|exists:users,email';
        $attributes['emailphone'] = 'Email';

        $valid = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $user = User::where('email', $request->emailphone)->first();
        if (!empty($user)) {
            $user->token = rand(100000, 999999);
            $user->save();
            $email = $request->emailphone;
            $token = $user->token;
            Mail::send('admin.mail.appForgotPassword',  compact('email', 'token'), function ($message) use ($email) {
                $message->to($email);
                $message->subject('Reset Password');
            });
            RemoveToken::dispatch($user)->delay(now()->addMinutes(30));

            return response()->json(['status' => true, 'Message' => "Reset Email send to {$email}", 'token' => $token, 'user' => $user,], 200);
        } else {
            return response()->json(['status' => false, 'Message' => "User not found"]);
        }
    }

    public function confirmOTP(Request $request)
    {
        $rules = [
            'token' => 'required',
            'emailphone' => 'required|email|exists:users,email',
        ];

        $messages = [
            'required' => 'This :attribute field is required',
            'email' => 'The :attribute must be a valid email address',
            'exists' => 'The :attribute does not exist in our records',
        ];

        $attributes = [
            'token' => 'Token',
            'emailphone' => 'Email',
        ];

        $valid = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $user = User::where('email', $request->emailphone)->first();

        if (empty($user)) {
            return response()->json(['status' => false, 'Message' => "User does not exist"], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['status' => false, 'Message' => "User already verified"], 200);
        }

        if (!$user->token) {
            return response()->json(['status' => false, 'Message' => "OTP does not exist. Please try to verify again"], 404);
        }

        if ($user->token != $request->token) {
            return response()->json(['status' => false, 'Message' => "OTP does not match. Please try to verify again"], 404);
        }

        $user->token = null;
        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'status' => true,
            'Message' => "Email Verified Successfully",
            'user' => $user,
        ], 200);
    }

    public function reset(Request $request)
    {
        $rules = [
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ];
        $messages = [
            'required' => 'This :attribute Field is Required',
        ];
        $attributes = [
            'token' => 'Token',
            'password' => 'Password',
            'c_password' => 'Confirm Password',
        ];

        $rules['emailphone'] = 'required|email|exists:users,email';
        $rules['emailphone'] = 'Email';
        $valid = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $user = User::where('email', $request->emailphone)->first();
        if (empty($user)) return response()->json(['status' => false, 'Message' => "User does not  exist"], 404);
        if (!$user->token) return response()->json(['status' => false, 'Message' => "OTP does not  exist. Please try to reset your password again"], 404);
        if ($user->token != $request->token) return response()->json(['status' => false, 'Message' => "OTP does not match"], 404);

        if (Hash::check($request->password, $user->password)) return response()->json(['status' => false, 'Message' => 'Please use different from current password.']);
        $user->password = Hash::make($request->password);
        $user->token = null;
        $user->save();
        return response()->json(['status' => true, 'Message' => "Password Reset Successfully", 'user' => $user,], 200);
    }

    public function edit_profile()
    {
        $user = User::with('role')->where('id', auth()->user()->id)->first();
        if (!empty($user)) return response()->json(['status' => true, 'Message' => 'User found', 'user' => $user ?? []], 200);
        else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function update_profile(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!empty($user)) {
            $rules = [
                'username' => 'required',
            ];
            $messages = [
                'required' => 'This :attribute Field is Required',
            ];
            $attributes = [
                'username' => 'Username',
            ];
            if ($user->role->name == 'retailer' || $user->role->name == 'wholesaler') {
                $rules['email'] = 'required|email|unique:users,email,' . auth()->user()->id;
                $rules['phone'] = 'required|digits:11|unique:users,phone,' . auth()->user()->id;
                $rules['business_name'] = 'required';
                $rules['business_address'] = 'required';
                $rules['province'] = 'required';
                $rules['country'] = 'required';
                $rules['shop_number'] = 'nullable';
                $rules['market_name'] = 'nullable';
                $rules['cnic_number'] = 'required|digits:13|unique:users,cnic_number,' . auth()->user()->id;
                $rules['cnic_image'] = 'nullable|array';
                $rules['bill_image'] = 'nullable|image';

                $attributes['email'] = 'Email';
                $attributes['phone'] = 'Phone';
                $attributes['business_name'] = 'Business Name';
                $attributes['business_address'] = 'Business Address';
                $attributes['province'] = 'Province';
                $attributes['country'] = 'Country';
                $attributes['shop_number'] = 'Shop Number';
                $attributes['market_name'] = 'Market Name';
                $attributes['cnic_number'] = 'CNIC Number';
                $attributes['cnic_image'] = 'CNIC Image';
                $attributes['bill_image'] = 'Bill Image';
            } else {
                if (is_numeric($request->get('emailphone'))) {
                    $rules['emailphone'] = 'required|digits:11|unique:users,phone,' . auth()->user()->id;
                    $attributes['emailphone'] = 'Phone';
                } else {
                    $rules['emailphone'] = 'required|email|unique:users,email,' . auth()->user()->id;
                    $attributes['emailphone'] = 'Email';
                }
            }
            $valid = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($valid->fails()) {
                return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
            }
            try {
                DB::beginTransaction();
                $user->username = $request->username;
                if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
                    $user->email = $request->email;
                    $user->phone = $request->phone;
                    $user->business_name = $request->business_name;
                    $user->business_address = $request->business_address;
                    $user->province = $request->province;
                    $user->country = $request->country;
                    $user->shop_number = $request->shop_number;
                    $user->market_name = $request->market_name;
                    $user->cnic_number = $request->cnic_number;
                    if (!empty($request->hasFile('bill_image'))) {
                        $image = $request->file('bill_image');
                        $filename = "BillImage-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                        $image->storeAs('bill', $filename, "public");
                        $user->bill_image = "bill/" . $filename;
                    }
                } else {
                    if (is_numeric($request->get('emailphone'))) {
                        $user->phone = $request->emailphone;
                    } else {
                        $user->email = $request->emailphone;
                    }
                    $user->first_name = $request->first_name;
                    $user->last_name = $request->last_name;
                    $user->address = $request->address;
                    $user->address2 = $request->address2;
                }
                if (!empty($request->image)) {
                    $image = $request->image;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $user->image = "image/" . $filename;
                }
                if (!$user->save()) throw new Error("User Not Updated");
                if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
                    if (!empty($request->cnic_image)) {
                        $cnic_images = CnicImage::where('user_id', $user->id)->get();
                        foreach ($cnic_images as $value) {
                            if (!$value->delete()) throw new Error("CNIC Images not deleted!");
                        }
                        foreach ($request->cnic_image as $key => $images) {
                            $cnic_image = new CnicImage();
                            $filename = "CNICImage-" . time() . "-" . rand() . "." . $images->getClientOriginalExtension();
                            $images->storeAs('cnic', $filename, "public");
                            $cnic_image->user_id = $user->id;
                            $cnic_image->cnic_image = "cnic/" . $filename;
                            if (!$cnic_image->save()) throw new Error("CNIC Images not added!");
                        }
                    }
                }
                $updatedUser = User::with(['role', 'cnic_image'])->where('id', $user->id)->first();
                DB::commit();
                return response()->json(['status' => true, 'Message' => "Profile Update Successfully", 'user' => $updatedUser,], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(['status' => false, 'Message' => $th->getMessage()]);
            }
        } else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function shopFollow(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'shop_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $followExist = FollowUserShop::where('user_id', auth()->user()->id)->where('shop_id', $request->shop_id)->first();
        if (is_object($followExist)) {
            if ($followExist->delete()) return response()->json(['status' => true, 'Message' => "Unfollow Successfully"], 200);
            return response()->json(['status' => false, 'Message' => "Unfollow not Successfull"]);
        }
        $follow = new FollowUserShop();
        $follow->user_id = auth()->user()->id;
        $follow->shop_id = $request->shop_id;
        if ($follow->save()) return response()->json(['status' => true, 'Message' => "Follow Successfully"], 200);
        return response()->json(['status' => false, 'Message' => "Follow not Successfull"]);
    }

    public function userBlock($id, $message = null)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $user = User::where('id', $id)->first();
        if (empty($user)) return response()->json(['status' => false, 'Message' => 'User not found']);
        if ($user->is_block == false) {
            $user->is_block = true;
            $title = 'YOU HAVE BEEN BLOCKED';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, '', $title, $message);
            if ($user->save()) return response()->json(['status' => true, 'Message' => 'User Block Successfully', 'User' => $user ?? []]);
        } else {
            $user->is_block = false;
            $title = 'YOU HAVE BEEN UNBLOCKED';
            $message = 'Dear ' . $user->username . ' you have been unblocked from admin-The Real Bazaar';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, '', $title, $message);
            if ($user->save()) return response()->json(['status' => true, 'Message' => 'User Unblock Successfully', 'User' => $user ?? []]);
        }
    }

    public function userDelete()
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!$user) return response()->json(['status' => false, 'Message' => 'User not found']);
        if ($user->delete()) return response()->json(['status' => true, 'Message' => 'User Deleted Successfully'], 200);
        else return response()->json(['status' => false, 'Message' => 'User not Delete']);
    }

    public function show($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $user = User::with('role')->where('id', $id)->first();
        if (!empty($user)) return response()->json(['status' => true, 'Message' => 'User found', 'user' => $user ?? []], 200);
        else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function signupDefaulter(Request $request)
    {
        $rules = [
            'role' => 'required',
            'name' => 'required',
            'password' => 'required',
        ];
        $messages = [
            'required' => 'This :attribute Field is Required',
        ];
        $attributes = [
            'role' => 'Role',
            'name' => 'Username',
            'password' => 'Password',
        ];
        $rules['email'] = 'required|email';
        $rules['phone'] = 'required|digits:11';
        $rules['business_name'] = 'required';
        $rules['business_address'] = 'required';
        $rules['province'] = 'required';
        $rules['country'] = 'required';
        $rules['shop_number'] = 'nullable';
        $rules['market_name'] = 'nullable';
        $rules['cnic_number'] = 'required|digits:13';
        $rules['txt_refno'] = 'required';
        $rules['payment_method'] = 'required';

        $attributes['email'] = 'Email';
        $attributes['phone'] = 'Phone';
        $attributes['business_name'] = 'Business Name';
        $attributes['business_address'] = 'Business Address';
        $attributes['province'] = 'Province';
        $attributes['country'] = 'Country';
        $attributes['shop_number'] = 'Shop Number';
        $attributes['market_name'] = 'Market Name';
        $attributes['cnic_number'] = 'CNIC Number';
        $attributes['txt_refno'] = 'TXTREFNO';
        $attributes['payment_method'] = 'Payment Method';
        $valid = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = new UnpaidRegisterUser();
            if ($request->role == 'user') $user->role_id = 3;
            if ($request->role == 'wholesaler') $user->role_id = 4;
            if ($request->role == 'retailer') $user->role_id = 5;
            $user->username =  $request->name;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->business_name = $request->business_name;
            $user->business_address = $request->business_address;
            $user->province = $request->province;
            $user->country = $request->country;
            $user->shop_number = $request->shop_number;
            $user->market_name = $request->market_name;
            $user->cnic_number = $request->cnic_number;
            $user->price = $request->price;
            $user->txt_refno = $request->txt_refno;
            $user->response_code = $request->response_code;
            $user->response_message = $request->response_message;
            $user->payment_method = $request->payment_method;
            if (!$user->save()) throw new Error("Something went wrong");
            $client = UnpaidRegisterUser::with(['role'])->where('id', $user->id)->first();
            DB::commit();
            return response()->json(['status' => true, 'Message' => "Something went wrong", 'user' => $client,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function subscribeEmail(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $valid = $valid->validated();
    }
    public function checkToken(Request $request)
    {
        $token = $request->input('token'); // Extract the token from the request

        if (!$token) {
            return response()->json(['status' => false, 'Message' => 'Token not provided'], 400);
        }

        try {
            // Use Passport's token repository to check the token
            $tokenIsValid = \Laravel\Passport\Token::where('id', explode('|', $token)[1] ?? null)
                ->where('revoked', false)
                ->exists();

            if ($tokenIsValid) {
                return response()->json(['status' => true, 'Message' => 'Token is valid'], 200);
            } else {
                return response()->json(['status' => false, 'Message' => 'Invalid token'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'Message' => 'Token validation failed', 'Error' => $e->getMessage()], 500);
        }
    }
}


// public function signupValidPage1(Request $request)
// {
//     $rules = [
//         'name' => 'required',
//         'email' =>  'required|email|unique:users,email',
//         'phone' => 'required|digits:11|unique:users,phone',
//     ];

//     $messages = [
//         'required' => 'This :attribute field is Required',
//     ];

//     $attributes = [
//         'name' => 'Username',
//         'email' => 'Email',
//         'phone' => 'Phone',
//     ];
//     $valid = Validator::make($request->all(), $rules, $messages, $attributes);
//     if ($valid->fails()) {
//         return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
//     } else {
//         return response()->json(['status' => true, 'Message' => 'Validation success'], 200);
//     }
// }

// public function signupValidPage2(Request $request)
// {
//     $rules = [
//         'business_name' => 'required',
//         'business_address' => 'required',
//         'province' => 'required',
//         'country' => 'required',
//         'cnic_number' => 'required|digits:13|unique:users,cnic_number',
//     ];

//     $messages = [
//         'required' => 'This :attribute field is Required',
//     ];

//     $attributes = [
//         'business_name' => 'Business Name',
//         'business_address' => 'Business Address',
//         'province' => 'Province',
//         'country' => 'Country',
//         'cnic_number' => 'CNIC Number',
//     ];
//     $valid = Validator::make($request->all(), $rules, $messages, $attributes);
//     if ($valid->fails()) {
//         return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
//     } else {
//         return response()->json(['status' => true, 'Message' => 'Validation success'], 200);
//     }
// }

// public function signupValidPage3(Request $request)
// {
//     $rules = [
//         'password' =>  'required',
//         'cnic_image' =>  'required|array',
//         'bill_image' => 'required|image',
//     ];

//     $messages = [
//         'required' => 'This :attribute field is Required',
//     ];

//     $attributes = [
//         'password' => 'Password',
//         'cnic_image' => 'CNIC Image',
//         'bill_image' => 'Bill Image',
//     ];
//     $valid = Validator::make($request->all(), $rules, $messages, $attributes);
//     if ($valid->fails()) {
//         return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
//     } else {
//         return response()->json(['status' => true, 'Message' => 'Validation success'], 200);
//     }
// }