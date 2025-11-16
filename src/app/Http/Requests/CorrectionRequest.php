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
            // 出勤・退勤（必須 & 時刻形式）
            'work_start' => ['required', 'date_format:H:i'],
            'work_end'   => ['required', 'date_format:H:i'],

            // 休憩（配列）
            'rests'                 => ['array'],
            'rest_start'    => ['nullable', 'date_format:H:i'],
            'rest_end'      => ['nullable', 'date_format:H:i'],

            // 備考欄（必須）
            'comment' => ['required', 'string'], // フィールド名は実装に合わせて
        ];
    }

    public function messages()
    {
        return [
            'work_start.required' => '出勤時間を入力してください',
            'work_start.date_format' => '出勤時間は「HH:MM」形式で入力してください',

            'work_end.required' => '退勤時間を入力してください',
            'work_end.date_format' => '退勤時間は「HH:MM」形式で入力してください',

            'rest_start.date_format' => '休憩開始時間は「HH:MM」形式で入力してください',
            'rest_end.date_format'   => '休憩終了時間は「HH:MM」形式で入力してください',
            'comment.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // フォームから送られてきた値をまず変数に入れる
            $workStart = $this->input('work_start'); // 例: "09:00"
            $workEnd   = $this->input('work_end');   // 例: "18:00"
            $rests     = $this->input('rests', []);  // 休憩の配列（なければ空の配列）

            /**
             * ① 出勤時間と退勤時間のチェック
             * - 両方入っている
             * - かつ 出勤時間 >= 退勤時間 のときエラー
             */
            if ($workStart && $workEnd && $workStart >= $workEnd) {
                $validator->errors()->add(
                    'work_start',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /**
             * ② 休憩時間のチェック
             * $rests は こんな配列イメージ：
             * [
             *   ['rest_start' => '12:00', 'rest_end' => '13:00'],
             *   ['rest_start' => '15:00', 'rest_end' => '15:30'],
             * ]
             */
            foreach ($rests as $index => $rest) {

                // その行の「休憩開始」と「休憩終了」を取り出す
                $restStart = $rest['rest_start'] ?? null;
                $restEnd   = $rest['rest_end'] ?? null;

                // --- 休憩開始時間のチェック ---
                if ($restStart) {

                    // 出勤時間より前ならエラー
                    if ($workStart && $restStart < $workStart) {
                        $validator->errors()->add(
                            "rests.$index.rest_start",
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 退勤時間より後ならエラー
                    if ($workEnd && $restStart > $workEnd) {
                        $validator->errors()->add(
                            "rests.$index.rest_start",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                // --- 休憩終了時間のチェック ---
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
