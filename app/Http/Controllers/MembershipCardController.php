<?php

namespace App\Http\Controllers;

use App\Models\MembershipCard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MembershipCardController extends Controller
{
    public function show(MembershipCard $card): View|RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $user->canAccessAdmin() || ($card->scope === 'library' && $user->role === User::ROLE_LIBRARIAN),
            403,
        );

        $card->load('cardable');

        if ($card->isLibraryCard() && $card->isActive()) {
            if ($card->card_printed) {
                return redirect()
                    ->route('library.members.show', $card->cardable)
                    ->with('error', 'این کارت کتاب‌خانه قبلاً چاپ شده است. تا پایان اعتبار شش‌ماهه، کارت جدید چاپ نمی‌شود؛ برای ورود، بیل ماهانه صادر کنید.');
            }

            $card->forceFill([
                'card_printed' => true,
                'printed_at' => now(),
            ])->save();
        }

        return view('cards.print', [
            'card' => $card,
        ]);
    }
}
