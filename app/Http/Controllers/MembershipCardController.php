<?php

namespace App\Http\Controllers;

use App\Models\MembershipCard;
use App\Models\User;
use Illuminate\View\View;

class MembershipCardController extends Controller
{
    public function show(MembershipCard $card): View
    {
        $user = auth()->user();

        abort_unless(
            $user->canAccessAdmin() || ($card->scope === 'library' && $user->role === User::ROLE_LIBRARIAN),
            403,
        );

        $card->load('cardable');

        return view('cards.print', [
            'card' => $card,
        ]);
    }
}
