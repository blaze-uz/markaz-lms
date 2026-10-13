<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->currentBranch) {
            $currentBranch = $user->branches()->first();
            if ($currentBranch) {
                $user->update(['current_branch_id' => $currentBranch->id]);
            }
        }

        return $next($request);
    }
}
