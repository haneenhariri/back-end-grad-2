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
    public function checkCompletion($courseId)
    {
        $allQuestions = Question::where('course_id', $courseId)->pluck('id');
        $answeredQuestions = UserAnswer::where('user_id', auth()->id())
            ->whereIn('question_id', $allQuestions)
            ->pluck('question_id');

        $unansweredQuestions = $allQuestions->diff($answeredQuestions);

        if ($unansweredQuestions->isEmpty()) {
            return [
                'completed' => true,
                'message' => 'لقد أجبت على جميع أسئلة الاختبار بالفعل',
                'unanswered_questions' => []
            ];
        }

        return [
            'completed' => false,
            'message' => 'هناك أسئلة لم يتم الإجابة عليها بعد',
            'unanswered_questions' => Question::whereIn('id', $unansweredQuestions)->get()
        ];
    }
}
