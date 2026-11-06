<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index(): JsonResponse
    {
        $loans = Loan::with(['member', 'items.book'])->get();
        return response()->json($loans, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
            'due_date' => 'required|date|after:today',
        ]);

        $member = Member::findOrFail($request->member_id);

        if ($member->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Member is suspended or inactive to borrow books.'
            ], 409);
        }

        return DB::transaction(function () use ($request, $member) {
            foreach ($request->book_ids as $bookId) {
                $book = Book::lockForUpdate()->find($bookId);

                if ($book->available_copies < 1) {
                    return response()->json([
                        'message' => "Book '{$book->title}' has no copies available."
                    ], 409);
                }
            }

            $loan = Loan::create([
                'member_id' => $member->id,
                'loan_date' => now()->toDateString(),
                'due_date' => $request->due_date,
                'status' => 'OPEN',
            ]);

            foreach ($request->book_ids as $bookId) {
                $book = Book::find($bookId);
                $book->decrement('available_copies');

                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book->id,
                    'status' => 'LOANED',
                ]);
            }

            return response()->json($loan->load(['member', 'items.book']), 201);
        });
    }

    public function show(Loan $loan): JsonResponse
    {
        return response()->json($loan->load(['member', 'items.book']), 200);
    }
}