<?php

namespace App\Http\Controllers;

use App\Services\MobileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new MobileService();
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $loginResult = $this->service->login($data);

        if ($loginResult instanceof JsonResponse) {
            return $loginResult;
        }

        [$user, $token, $branch] = $loginResult;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'branch' => $branch,
        ]);
    }

    public function me()
    {
        $data = $this->service->me();

        return response()->json($data);
    }

    public function logout()
    {
        $this->service->logout();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function branches()
    {
        $branches = $this->service->getBranches();

        return response()->json($branches);
    }

    public function changeCurrentBranch(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $this->service->changeCurrentBranch($data);

        return response()->json([
            'message' => 'Current branch changed successfully',
        ]);
    }

    public function calendar()
    {
        $data = $this->service->getCalendarData(request()->date ?? now()->format('Y-m-d'));

        return response()->json([
            'events' => $data,
        ]);
    }

    public function roomTimeline()
    {
        $data = $this->service->getRoomTimeline(request()->room);

        return response()->json($data);
    }

    public function groups()
    {
        $cardData = $this->service->getGroupsCardData();
        $data = $this->service->getGroups();

        return response()->json([
            'card_data' => $cardData,
            'groups' => $data,
        ]);
    }

    public function showGroup($group)
    {
        $data = $this->service->getGroupData($group);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function groupCardData($group)
    {
        $data = $this->service->getGroupCardData($group);

        return response()->json($data);
    }

    public function groupStudents($group)
    {
        $data = $this->service->getGroupStudents($group);

        return response()->json([
            'students' => $data,
        ]);
    }

    public function groupAttendance($group, Request $request)
    {
        $data = $this->service->getGroupAttendance($group, $request->range ?? '7');

        return response()->json($data);
    }

    public function storeAttendance($group, Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'students' => 'required|array',
            'students.*.id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent',
            'students.*.score' => 'sometimes',
            'students.*.comment' => 'sometimes',
        ]);

        json_encode($data['students']);

        $response = $this->service->storeAttendance($group, $data);

        return response()->json($response);
    }

    public function updateAttendance($group, Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'students' => 'required|array',
            'students.*.id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent',
            'students.*.score' => 'sometimes',
            'students.*.comment' => 'sometimes',
        ]);

        json_encode($data['students']);

        $this->service->updateAttendance($group, $data);

        return response()->json([
            'message' => 'Attendance updated successfully',
        ]);
    }

    public function getAttendance($group, Request $request)
    {
        $date = $request->input('date') ?? now()->format('Y-m-d');
        $data = $this->service->getAttendance($group, $date);

        return response()->json([
            'students' => $data,
        ]);
    }

    public function dashboard()
    {
        $data = $this->service->getDashboardData();

        return response()->json($data);
    }
}
