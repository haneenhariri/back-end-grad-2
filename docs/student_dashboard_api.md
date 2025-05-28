# توثيق API لوحة تحكم الطالب

## نقاط النهاية المتاحة

### الإحصائيات العامة
- **URL**: `/api/student/dashboard/general-stats`
- **Method**: `GET`
- **الوصف**: يوفر إحصائيات عامة عن نشاط الطالب مثل عدد الكورسات المسجلة والمكتملة.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "enrolled_courses": 5,
      "completed_courses": 2,
      "total_lessons_watched": 45,
      "average_progress": 68.5
    }
  }
  ```

### إحصائيات التقدم
- **URL**: `/api/student/dashboard/progress-stats`
- **Method**: `GET`
- **الوصف**: يوفر تفاصيل عن تقدم الطالب في كل كورس.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "course_id": 1,
        "course_title": "مقدمة في البرمجة",
        "progress_percentage": 75,
        "completed_lessons": 9,
        "last_activity": "2023-05-15T10:30:00Z"
      },
      ...
    ]
  }
  ```

### نتائج الاختبارات
- **URL**: `/api/student/dashboard/exam-results`
- **Method**: `GET`
- **الوصف**: يوفر ملخص لنتائج الطالب في اختبارات الكورسات.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "course_id": 1,
        "course_title": "مقدمة في البرمجة",
        "score": 18,
        "total_questions": 20,
        "percentage": 90
      },
      ...
    ]
  }
  ```

### النشاط الأخير
- **URL**: `/api/student/dashboard/recent-activity`
- **Method**: `GET`
- **الوصف**: يوفر قائمة بأحدث نشاطات الطالب.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "type": "progress",
        "course_id": 1,
        "course_title": "مقدمة في البرمجة",
        "details": "تقدم بنسبة 75%",
        "date": "2023-05-15T10:30:00Z"
      },
      {
        "type": "exam",
        "course_id": 2,
        "course_title": "تطوير تطبيقات الويب",
        "details": "إجابة على سؤال بعلامة 1",
        "date": "2023-05-14T14:20:00Z"
      },
      ...
    ]
  }
  ```

### الكورسات الموصى بها
- **URL**: `/api/student/dashboard/recommended-courses`
- **Method**: `GET`
- **الوصف**: يوفر قائمة بالكورسات الموصى بها للطالب بناءً على اهتماماته.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "id": 5,
        "title": "تطوير تطبيقات الهاتف المحمول",
        "price": 299,
        "students_count": 45,
        "average_rating": 4.8,
        "instructor": { "id": 3, "name": "محمد أحمد" },
        "category": { "id": 2, "name": "تطوير التطبيقات" }
      },
      ...
    ]
  }
  ```