<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as HttpRequest;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin =  $user->hasAnyRole(['Admin']);

        // $query = Appointment::with('user', 'post')
        // ->orderBy('id', 'desc');
        // if ($request->filled('from_date') && $request->filled('to_date')) {
        //     $query->whereBetween('requestDate', [$request->from_date, $request->to_date]);
        // }
        // $appointments = $query->paginate(6);
        if ($user->hasRole('Admin')) {
            $appointments = Appointment::query()
                ->with(['user', 'post'])
                ->orderBy('created_at', 'desc')
                ->when(HttpRequest::input('search'), function ($query, $search) {
                    $query->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('post', function ($postQuery) use ($search) {
                        $postQuery->where('name', 'like', '%' . $search . '%');
                    });
                });

            if (HttpRequest::filled('from_date') && HttpRequest::filled('to_date')) {
                $appointments->whereBetween('date', [HttpRequest::input('from_date'), HttpRequest::input('to_date')]);
            }

            $appointments = $appointments->paginate(8)->withQueryString();
        }
         elseif ($user->hasRole('Standard')) {
            $appointments = Appointment::with(['user', 'post'])
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }) ->orderBy('created_at', request('sort', 'desc'))
                ->when(HttpRequest::input('search'), function($query, $search){
                    $query->whereHas('user', function($userQuery) use ($search){
                        $userQuery->where('name',  'like' , '%' . $search . '%');
                    })->orWhereHas('post', function ($postQuery) use ($search) {
                        $postQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
                if (HttpRequest::filled('from_date') && HttpRequest::filled('to_date')) {
                    $appointments->whereBetween('date', [HttpRequest::input('from_date'), HttpRequest::input('to_date')]);
                }

                $appointments = $appointments->paginate(8)->withQueryString();

        }  else {
            // Handle other roles or unauthorized access as needed
            return abort(403);
        }


        return inertia('Appointment/Index', [
            'appointments' => $appointments,

            'filters' => HttpRequest::only(['search']),
            'isAdmin' => $isAdmin
        ]);
    }

    public function create(){
        $user = Auth::user();
        $posts = Post::all();
        $isAdmin =  $user->hasAnyRole(['Admin']);
        $isStandard =  $user->hasRole('Standard');
        // $users = User::where('type', "Standard")->get();
        $users = User::where('type', 'Standard')->get();

        return inertia('Appointment/Create', [
            'posts' => $posts,
            'isAdmin' => $isAdmin,
            'isStandard'=> $isStandard,
            'users' => $users,
            'user'=> $user
        ]);
    }

    public function store(Request $request){
        $user = Auth::user();

        $fields = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'days_stay' => 'required',
            'person_number' => 'required',
        ]);

        if ($user->hasRole('Standard')) {
            $fields['user_id'] = $user->id;
        } elseif ($user->hasRole('Admin')) {
            // If the user is an Admin, expect the doc_id from the form
            $fields['user_id'] = $request->input('user_id');
            $fields['status'] = "Accepted";
        }


       $appointment =  Appointment::create($fields);
    //    $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " created an appointment with ". $appointment->patient->firstname. " " . $appointment->patient->lastname. " with the id# ". $appointment->id;
    //    event(new UserLog($log_entry));

        return redirect()->route('appointment.index')->with('success', 'Appointment created successfully.');
    }
    public function calendar(){
        // $appointments = Appointment::with('patient', 'post', 'doctor.user')->orderBy('date', 'asc')->get();
        $appointments = Appointment::with('user', 'post')
        ->orderBy('created_at', 'desc')
        ->get();


        return response()->json($appointments);
    }

    public function accept(Appointment $appointment){
        $appointment->update(['status' => 'Accepted']);

        $user = $appointment->user;



        // $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " accepted an appointment with ". $appointment->patient->firstname. " " . $appointment->patient->lastname. " with the id# ". $appointment->id;
        // event(new UserLog($log_entry));

        return redirect()->route('appointment.index')->with('success', 'Appointment approved successfully.');
    }

    public function cancel(Appointment $appointment){
        // Update the appointment status to 'canceled'
        $appointment->update(['status' => 'Cancelled']);

        $user = $appointment->user;



        // $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " cancelled an appointment with ". $appointment->patient->firstname. " " . $appointment->patient->lastname. " with the id# ". $appointment->id;
        // event(new UserLog($log_entry));

        return redirect()->route('appointment.index')->with('success', 'Appointment canceled successfully.');
    }

    public function withpost(Post $post = null){
        $user = Auth::user();

        $isAdmin =  $user->hasAnyRole(['Admin']);
        $isStandard =  $user->hasRole('Standard');
        $users = User::where('type', 'Standard')->get();

        if ($post) {
            $posts = [$post];
        } else {
            $posts = Post::all();
        }

        return inertia('Appointment/Create2', [
            'post' => $posts,
            'isAdmin' => $isAdmin,
            'isStandard'=> $isStandard,
            'users' => $users,
            'user'=> $user
        ]);
    }

    public function storePost(Request $request){
        $user = Auth::user();

        $fields = $request->validate([

            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'days_stay' => 'required',
            'person_number' => 'required',
        ]);

        if ($user->hasRole('Standard')) {
            $fields['user_id'] = $user->id;
        } elseif ($user->hasRole('Admin')) {

            $fields['user_id'] = $request->input('user_id');
            $fields['status'] = "Accepted";
        }


        if ($request->has('post_id')) {
            $fields['post_id'] = $request->input('post_id');
            $post = Post::find($fields['post_id']);
            $successMessage = 'Post booked successfully.';
        } else {
            // No need to fetch posts again, as it was done in the withpost method
            $successMessage = 'All Posts retrieved successfully.';
        }


       $appointment =  Appointment::create($fields);


        return redirect()->route('appointment.index')->with('success', 'Appointment created successfully.');
    }
}

