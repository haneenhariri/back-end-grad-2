public function enrollCourse(Request $request)
{
    try {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'required|in:credit_card,bank_transfer,wallet',
            'transaction_id' => 'required_if:payment_method,credit_card,bank_transfer',
            'receipt_image' => 'required_if:payment_method,bank_transfer|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userId = auth()->id();
        $courseId = $request->course_id;
        
        // التحقق مما إذا كان الطالب مسجل بالفعل في الكورس
        $existingEnrollment = \DB::table('course_enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingEnrollment) {
            return self::error('أنت مسجل بالفعل في هذا الكورس', 400);
        }
        
        // الحصول على معلومات الكورس
        $course = \DB::table('courses')->find($courseId);
        if (!$course) {
            return self::error('الكورس غير موجود', 404);
        }
        
        // تحديد سعر الكورس (مع الخصم إن وجد)
        $price = $course->discount_price ?? $course->price;
        
        // معالجة الدفع حسب طريقة الدفع
        $paymentStatus = 'pending';
        $paymentDetails = [];
        
        switch ($request->payment_method) {
            case 'credit_card':
                // التحقق من معرف المعاملة
                if (!$request->transaction_id) {
                    return self::error('معرف المعاملة مطلوب للدفع ببطاقة الائتمان', 400);
                }
                
                $paymentStatus = 'completed'; // يمكن تغييره حسب التحقق من API بوابة الدفع
                $paymentDetails = [
                    'transaction_id' => $request->transaction_id,
                    'payment_method' => 'credit_card'
                ];
                break;
                
            case 'bank_transfer':
                // التحقق من صورة الإيصال
                if (!$request->hasFile('receipt_image')) {
                    return self::error('صورة إيصال التحويل البنكي مطلوبة', 400);
                }
                
                // تخزين صورة الإيصال
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
                
                $paymentStatus = 'pending_verification'; // بانتظار التحقق من الإدارة
                $paymentDetails = [
                    'transaction_id' => $request->transaction_id,
                    'receipt_image' => $receiptPath,
                    'payment_method' => 'bank_transfer'
                ];
                break;
                
            case 'wallet':
                // التحقق من رصيد المحفظة
                $wallet = \DB::table('wallets')->where('user_id', $userId)->first();
                
                if (!$wallet || $wallet->balance < $price) {
                    return self::error('رصيد المحفظة غير كافٍ', 400);
                }
                
                // خصم المبلغ من المحفظة
                \DB::table('wallets')
                    ->where('user_id', $userId)
                    ->update(['balance' => $wallet->balance - $price]);
                    
                // تسجيل معاملة المحفظة
                \DB::table('wallet_transactions')->insert([
                    'user_id' => $userId,
                    'amount' => -$price,
                    'type' => 'course_purchase',
                    'description' => 'شراء كورس: ' . $course->title,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $paymentStatus = 'completed';
                $paymentDetails = [
                    'payment_method' => 'wallet',
                    'wallet_transaction_id' => \DB::getPdo()->lastInsertId()
                ];
                break;
        }
        
        // إنشاء سجل الدفع
        $paymentId = \DB::table('payments')->insertGetId([
            'user_id' => $userId,
            'amount' => $price,
            'status' => $paymentStatus,
            'details' => json_encode($paymentDetails),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // تسجيل الطالب في الكورس
        \DB::table('course_enrollments')->insert([
            'user_id' => $userId,
            'course_id' => $courseId,
            'payment_id' => $paymentId,
            'enrollment_date' => now(),
            'status' => $paymentStatus === 'completed' ? 'active' : 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إرسال إشعار للطالب
        $notificationMessage = $paymentStatus === 'completed' 
            ? 'تم تسجيلك بنجاح في الكورس: ' . $course->title
            : 'تم استلام طلب التسجيل في الكورس: ' . $course->title . ' وهو قيد المراجعة';
            
        \DB::table('notifications')->insert([
            'user_id' => $userId,
            'title' => 'تسجيل كورس',
            'message' => $notificationMessage,
            'read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return self::success([
            'enrollment_status' => $paymentStatus === 'completed' ? 'active' : 'pending',
            'payment_status' => $paymentStatus,
            'course_id' => $courseId,
            'message' => $notificationMessage
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error enrolling in course', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return self::error('حدث خطأ أثناء التسجيل في الكورس: ' . $e->getMessage());
    }
}