<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TestCleanupController extends Controller
{
    public function cleanup(): JsonResponse
    {
        // Delete test users created during Playwright runs
        // Identifies them by email pattern used in tests
        $testUsers = User::where('email', 'like', 'newuser%@test.com')
            ->orWhere('email', 'like', 'playwright%@test.com')
            ->orWhere('email', 'like', 'behat%@test.com')
            ->get();

        foreach ($testUsers as $user) {
            Payment::where('user_id', $user->id)->delete();
            Order::where('user_id', $user->id)->delete();
            $user->delete();
        }

        return response()->json([
            'message' => 'Test data cleaned up successfully',
            'deleted_users' => $testUsers->count(),
        ]);
    }
}
