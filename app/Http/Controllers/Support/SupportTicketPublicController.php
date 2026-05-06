<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestSupportTicketRequest;
use App\Services\Support\SupportTicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SupportTicketPublicController extends Controller
{
    public function __construct(
        private readonly SupportTicketService $service,
    ) {
    }

    public function create(): View
    {
        return view('support.public.create');
    }

    public function store(StoreGuestSupportTicketRequest $request): RedirectResponse
    {
        $ticket = $this->service->createGuestTicket($request->validated());

        return redirect()
            ->route('support.public.thanks')
            ->with('ticket_id', $ticket->id);
    }

    public function thanks(): View
    {
        return view('support.public.thanks');
    }
}
