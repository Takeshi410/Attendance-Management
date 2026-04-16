<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in_at' => ['required', 'date_format:H:i', 'before:clock_out_at'],
            'clock_out_at' => ['required', 'date_format:H:i'],
            'remarks' => ['required'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start_at' => ['required_with:breaks', 'date_format:H:i', 'after:clock_in_at', 'before:breaks.*.break_end_at'],
            'breaks.*.break_end_at' => ['required_with:breaks', 'date_format:H:i', 'before:clock_out_at'],
            'new_break' => ['nullable', 'array'],
            'new_break.break_start_at' => ['nullable', 'required_with:new_break.break_end_at', 'date_format:H:i', 'after:clock_in_at', 'before:new_break.break_end_at'],
            'new_break.break_end_at' => ['nullable', 'required_with:new_break.break_start_at', 'date_format:H:i', 'before:clock_out_at'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in_at.required' => '出勤時間を入力してください',
            'clock_in_at.date_format' => '出勤時間は hh:mm 形式（例：09:30）で入力してください',
            'clock_in_at.before' => '出勤時間が不適切な値です',
            'clock_out_at.required' => '退勤時間を入力してください',
            'clock_out_at.date_format' => '退勤時間は hh:mm 形式（例：09:30）で入力してください',
            'remarks.required' => '備考を記入してください',
            'breaks.*.break_start_at.required_with' => '休憩開始時間を入力してください',
            'breaks.*.break_start_at.date_format' => '休憩時間は hh:mm 形式（例：09:30）で入力してください',
            'breaks.*.break_start_at.after' => '開始時間もしくは休憩時間が不適切な値です',
            'breaks.*.break_start_at.before' => '休憩時間が不適切な値です',
            'breaks.*.break_end_at.required_with' => '休憩終了時間を入力してください',
            'breaks.*.break_end_at.date_format' => '休憩時間は hh:mm 形式（例：09:30）で入力してください',
            'breaks.*.break_end_at.before' => '退勤時間もしくは休憩時間が不適切な値です',
            'new_break.break_start_at.date_format' => '休憩時間は hh:mm 形式（例：09:30）で入力してください',
            'new_break.break_start_at.required_with' => '休憩開始時間を入力してください',
            'new_break.break_start_at.after' => '開始時間もしくは休憩時間が不適切な値です',
            'new_break.break_start_at.before' => '休憩時間が不適切な値です',
            'new_break.break_end_at.date_format' => '休憩時間は hh:mm 形式（例：09:30）で入力してください',
            'new_break.break_end_at.required_with' => '休憩終了時間を入力してください',
            'new_break.break_end_at.before' => '退勤時間もしくは休憩時間が不適切な値です',
        ];
    }
}
