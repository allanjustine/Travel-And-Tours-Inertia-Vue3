<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as HttpRequest;


class PostController extends Controller
{
    public function index()
    {
        $postCount = Post::count();
        // $posts = Post::with('doctors')->get();
        $posts = Post::query()
            ->when(HttpRequest::input('search'), function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('location', 'like', '%' . $search . '%')
                ->orWhere('hotel_name', 'like', '%' . $search . '%')
                ->orWhere('price', 'like', '%' . $search . '%');
            })
            ->paginate(8)
            ->withQueryString();
        return inertia('Post/Index', [
            'posts' => $posts,
            'postCount' => $postCount,
            'filters' => HttpRequest::only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'location' => 'required',
            'hotel_name' => 'required',
            'price' => 'required',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10240',
            'description' => 'required',
        ]);

        $fileName = null;
        if ($request->hasFile('image')) {
            $fileName = time() . "." . $request->image->extension();
            $request->image->move(public_path('travels/'), $fileName);
            $fields['image'] = $fileName; // Fix: Use $fields instead of $radiologicData
        }
        $post =   Post::create($fields);
        // $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " created a post - " . $post->name;
        // event(new UserLog($log_entry));
        return redirect('/post')->with('success', $post->hotel_name . ' Post successfully created');
    }

    public function update(Request $request, Post $post)
    {
        $fields = $request->validate([
            'name' => 'string',
            'location' => 'string',
            'hotel_name' => 'string',
            'price' => 'string',
            'image' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10240',
            'description' => 'string',
        ]);

        $fileName = null;
        if ($request->hasFile('image')) {
            $fileName = time() . "." . $request->image->extension();
            $request->image->move(public_path('posts/'), $fileName);
            $fields['image'] = $fileName; // Fix: Use $fields instead of $radiologicData
        }
        $post->update($fields);
        // $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " updated a post - " . $post->name;
        // event(new UserLog($log_entry));
        return redirect('/post')->with('success', $post->hotel_name . " Post successfully updated");
    }

    public function destroy(Post $post)
    {
        $post->delete();
        // $log_entry = Auth::user()->firstname . " ". Auth::user()->lastname . " deleted a post - " . $post->name;
        // event(new UserLog($log_entry));
        return back()->with('success', $post->hotel_name . " Post successfully deleted");
    }
}
