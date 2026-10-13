<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Sms;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function getReceivers(Request $request)
    {
        if ($request->sendSmsType == 'staff') {
            $ids = explode(',', $request->sendSmsIds);
            $receivers = User::whereIn('id', $ids)->orderBy('name')->get();
        } elseif ($request->sendSmsType == 'leads') {
            if ($request->filled('sendSmsSelection')) {
                $ids = session('lead_sms_selection.'.$request->sendSmsSelection, []);
            } else {
                $ids = json_decode($request->sendSmsIds, true);
            }

            if (! is_array($ids) || $ids === []) {
                Log::error('Failed to decode leads IDs');
                abort(404);
            }

            $receivers = Lead::withTrashed()->whereIn('id', $ids)->orderBy('name')->get();
        } else {
            $ids = json_decode($request->sendSmsIds, true);
            if (! $ids) {
                abort(404);
            }
            $receivers = Student::whereIn('id', $ids)->orderBy('name')->get();
        }

        return $receivers;
    }

    public function delete($ids, bool $selectAll = false, ?Request $request = null): void
    {
        if ($selectAll && $request) {
            $this->buildFilteredQuery($request)
                ->where('status', 'pending')
                ->delete();
        } else {
            $ids = explode(',', $ids);
            Sms::whereIn('id', $ids)
                ->where('status', 'pending')
                ->delete();
        }
    }

    public function returnToPending($ids, bool $selectAll = false, ?Request $request = null): void
    {
        if ($selectAll && $request) {
            $this->buildFilteredQuery($request)->update(['status' => 'pending']);
        } else {
            $ids = explode(',', $ids);
            Sms::whereIn('id', $ids)->update(['status' => 'pending']);
        }
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = Sms::query();

        if ($request->has('selectedStatus') && $request->selectedStatus) {
            $query->where('status', $request->selectedStatus);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('phone', 'ilike', '%'.$request->search.'%')
                    ->orWhere('content', 'ilike', '%'.$request->search.'%');
            });
        }

        if ($request->has('date') && $request->date) {
            $dates = explode(' - ', $request->date);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('Y.m.d', $dates[0])->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromFormat('Y.m.d', $dates[1])->format('Y-m-d');
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }
        }

        return $query->where('status', '!=', 'scheduled');
    }
}
