<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\ReceiptSetting;
use App\Models\Settings;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new PaymentService();
        $this->middleware('permission:payment-index')->only(['index', 'sendSms']);
        $this->middleware('permission:payment-index|payment-show')->only('show');
        $this->middleware('permission:payment-edit')->only(['edit', 'update']);
        $this->middleware('permission:payment-refund')->only(['refund', 'refundStore']);
        // A receipt is printed from the payments list, right after a payment
        // is taken (StudentPaymentController needs student-payment), from the
        // payment modal on a student's page (student-show), and by staff who
        // see their groups' payments (user-payment-list).
        $this->middleware('permission:payment-index|student-payment|student-show|user-payment-list')->only('print');
    }

    public function index()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return redirect()->route('admin.users.payments');
        }

        return view('admin.payments.index');
    }

    public function show($id)
    {
        $payment = $this->service->find($id, ['student', 'payment_type', 'transaction', 'branch', 'payment_discounts.transaction']);

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load('student', 'payment_type', 'transaction', 'branch', 'payment_discounts.transaction');
        $paymentTypes = PaymentType::all();

        return view('admin.payments.edit', compact('payment', 'paymentTypes'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'payment_type_id' => 'required',
        ]);
        $this->service->update($data, $id);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.payments.index');
    }

    public function discount()
    {
        return view('admin.payments.discount');
    }

    public function refund($id)
    {
        $item = $this->service->getRefundItem($id);

        return view('admin.payments.refund', compact('item'));
    }

    public function refundStore($id)
    {
        try {
            $this->service->refund($id);
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());

            return redirect()->back();
        }
        session()->flash('success_alert', __('translation.alert_messages.refund'));

        return redirect()->route('admin.payments.index');
    }

    public function print($id)
    {
        $item = Payment::with(['student', 'student_group_payment.group.teacher', 'payment_type', 'creator'])->findOrFail($id);
        $settings = Settings::whereIn('key', ['address', 'phone', 'logo'])->pluck('value', 'key');

        $receiptSettings = ReceiptSetting::first();
        if (! $receiptSettings) {
            $defaultHeaderText = __('translation.address').": {$settings['address']}\n".__('translation.phone').": {$settings['phone']}";
            $receiptSettings = ReceiptSetting::create([
                'header_text' => $defaultHeaderText,
                'show_logo' => true,
                'show_header_text' => true,
                'show_payment_id' => true,
                'show_student_info' => true,
                'show_group_info' => true,
                'show_payment_type' => true,
                'show_cashier' => true,
                'show_amount' => true,
                'show_footer' => true,
                'logo_position' => 'bottom',
            ]);
        }

        $logo = '';
        if ($receiptSettings->show_logo) {
            $settingsLogo = Settings::where('key', 'logo')->first();
            $logo = $settingsLogo?->getFirstMediaUrl() ?: asset('images/markaz-lms.svg');
        }

        return view('admin.payments.print', compact('item', 'settings', 'logo', 'receiptSettings'));
    }

    public function sendSms($id)
    {
        $this->service->sendSms($id);
        session()->flash('success_alert', __('translation.alert_messages.sms_sent'));

        return redirect()->back();
    }
}
