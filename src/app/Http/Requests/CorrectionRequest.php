<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
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
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],

            'rests' => ['array'],
            'rests.*.rest_start' => ['nullable', 'date_format:H:i'],
            'rests.*.rest_end'   => ['nullable', 'date_format:H:i'],

            'comment' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_start.required' => '出勤時間を入力してください',
            'work_start.date_format' => '出勤時間は「HH:MM」形式で入力してください',

            'work_end.required' => '退勤時間を入力してください',
            'work_end.date_format' => '退勤時間は「HH:MM」形式で入力してください',

            'rests.*.rest_start.date_format' => '休憩開始時間は「HH:MM」形式で入力してください',
            'rests.*.rest_end.date_format'   => '休憩終了時間は「HH:MM」形式で入力してください',
            'comment.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // フォームから送られてきた値をまず変数に入れる
            $workStart = $this->input('work_start');
            $workEnd   = $this->input('work_end');
            $rests     = $this->input('rests', []);

            //出勤時間と退勤時間のチェック
            // 両方入っている かつ 出勤時間 >= 退勤時間 のときエラー
            if ($workStart && $workEnd && $workStart >= $workEnd) {
                $validator->errors()->add(
                    'work_start',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 休憩時間のチェック
            foreach ($rests as $index => $rest) {

                // 休憩開始と終了を取り出す
                $restStart = $rest['rest_start'] ?? null;
                $restEnd   = $rest['rest_end'] ?? null;

                // 休憩開始時間のチェック
                if ($restStart) {

                    // 出勤時間より前ならエラー
                    if ($workStart && $restStart < $workStart) {
                        $validator->errors()->add(
                            "rests.$index.rest_start",
                            '休憩時間が勤務時間外です'
                        );
                    }

                    // 退勤時間より後ならエラー
                    if ($workEnd && $restStart > $workEnd) {
                        $validator->errors()->add(
                            "rests.$index.rest_start",
                            '休憩時間が勤務時間外です'
                        );
                    }
                }

                // 休憩終了時間のチェック
                if ($restEnd) {

                    // 退勤時間より後ならエラー
                    if ($workEnd && $restEnd > $workEnd) {
                        $validator->errors()->add(
                            "rests.$index.rest_end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}
