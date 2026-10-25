<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function open(string $id)
    {
        $n = auth()->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return redirect($n->data['url'] ?? '/');
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
