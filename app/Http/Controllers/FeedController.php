<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index() {
        $users = User::all();
        $messages = Messages::all();
        return view('tables', compact('users', 'messages'));
    }
}
