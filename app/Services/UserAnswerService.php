<?php

namespace App\Services;

use App\Models\Question;
use App\Models\UserAnswer;

class UserAnswerService
{
    public function store($data){
        $question = Question::findOrFail($data['question_id']);
        if ($question->type == 'multipleChoice') {
            $data['mark'] = $data['answer'] == $question->correct_answer ? $question->mark : 0;
        }
        auth()->user()->questions()->syncWithoutDetaching([
            $question->id => $data
        ]);
    }
    public function update($mark,UserAnswer $userAnswer){
        if($mark > $userAnswer->question->mark){
            throw new \Exception('الدرجة المضافة غير صحيحة. يرجى التحقق وإعادة المحاولة');
        }
        $userAnswer->update(['mark' => $mark]);
        return $userAnswer;
    }


    public function testResult($courseId){
        $questionsIds = Question::where('course_id', $courseId)->pluck('id');
        $userAnswersQuery = UserAnswer::where('user_id', auth()->id())
            ->whereIn('question_id', $questionsIds);

        if ($userAnswersQuery->clone()->whereNull('mark')->exists()) {
            return "لم يتم تصحيح الأسئلة من قبل المدرس";
        }
        return $userAnswersQuery->sum('mark');
    }
}
