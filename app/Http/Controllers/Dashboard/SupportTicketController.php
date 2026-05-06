<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly SupportTicketService $service,
    ) {
    }

    public function index(): View
    {
        $user = Auth::user();

        $query = SupportTicket::query()->orderByDesc('id');
        $isAdmin = method_exists($user, 'isAdmin') ? (bool)$user->isAdmin() : false;
        if (!$isAdmin) {
            $query->where('user_id', '=', $user->id);
        }
        $tickets = $query->paginate(10);

        return view('support-tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('support-tickets.create');
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $ticket = $this->service->createTicket(
            Auth::user(),
            $request->validated(),
        );

        return redirect()
            ->route('support-tickets.show', ['ticket' => $ticket->id])
            ->with('success', 'Ticket creado. Nuestro equipo lo revisara pronto.');
    }

    public function show(SupportTicket $ticket): View
    {
        $user = Auth::user();
        $isAdmin = method_exists($user, 'isAdmin') ? (bool)$user->isAdmin() : false;

        if (!$isAdmin && (int)$ticket->user_id !== (int)$user->id) {
            abort(403);
        }

        return view('support-tickets.show', compact('ticket'));
    }
}
