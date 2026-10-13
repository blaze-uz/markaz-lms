<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\PaymentRequest;
use App\Models\Account;
use App\Models\PaymentType;
use App\Services\StudentPaymentService;

class StudentPaymentController extends Controller
{
    public StudentPaymentService $service;

    public function __construct()
    {
        $this->service = new StudentPaymentService();
        $this->middleware('permission:student-payment');
    }

    public function create($student_id)
    {
        //item is student
        $item = $this->service->getStudent($student_id);
        $unpaidMonths = $this->service->getUnpaidMonths($student_id);
        $main_account = $this->service->getMainAccount();
        $discountAccounts = $this->service->getDiscountAccounts();
        $paymentTypes = PaymentType::orderBy('position')->get();
        $branches = $this->service->getBranches();
        $paymentSmsStatus = settings('payment_sms_status');
        $paymentTextSuggestions = $this->service->getPaymentTextSuggestions();
        $commentTextSuggestions = $this->service->getCommentTextSuggestions();
        return view('admin.students.payment', [
            'item' => $item,
            'unpaidMonths' => $unpaidMonths,
            'discountAccounts' => $discountAccounts,
            'paymentTypes' => $paymentTypes,
            'branches' => $branches,
            'paymentSmsStatus' => $paymentSmsStatus,
            'paymentTextSuggestions' => $paymentTextSuggestions,
            'commentTextSuggestions' => $commentTextSuggestions,
            'main_account' => $main_account
        ]);
    }

    public function store(PaymentRequest $request, $id)
    {
        try {
            $paymentAction = $this->service->payment($request->validated(), $id);
            if ($paymentAction && $paymentAction['status'] == 'error') {
                session()->flash("error_alert", $paymentAction['message']);
                return redirect()->back();
            } else {
                session()->flash("success_alert", __("translation.alert_messages.payment"));
                return redirect()->route('admin.payments.print', $paymentAction['payment_id']);
            }
        } catch (\Exception $e) {
            logger()->error('Failed to create payment: ' . $e->getMessage());
            session()->flash("error_alert", $e->getMessage());
            return redirect()->back();
        }
    }

    public function edit($payment_id)
    {
        $payment = $this->service->getPayment($payment_id);
        $item = $this->service->getStudent($payment->student_id);
        $unpaidMonths = $this->service->getUnpaidMonths($payment->student_id, 'paymentEdit');
        $discountAccounts = Account::all();
        $paymentTypes = PaymentType::all();
        return view("admin.students.payment-edit", compact('item', 'unpaidMonths', 'discountAccounts', 'payment', 'paymentTypes'));
    }

    /**
     * @throws \Exception
     */
    public function update(PaymentRequest $request, $payment_id)
    {
        $paymentUpdateAction = $this->service->paymentUpdate($request->validated(), $payment_id);
        session()->flash("success_alert", __("translation.alert_messages.payment_updated"));
        if ($paymentUpdateAction && $paymentUpdateAction['status'] == 'error') {
            return redirect()->back();
        } else {
            return redirect()->route('admin.students.show', $paymentUpdateAction['student_id']);
        }
    }

    /**
     * @throws \Exception
     */
    public function delete($payment_id)
    {
        $this->service->paymentDelete($payment_id);
        session()->flash("success_alert", __("translation.alert_messages.payment_deleted"));
        return redirect()->back();
    }
}
