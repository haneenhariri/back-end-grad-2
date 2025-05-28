# توثيق API لوحة تحكم الأدمن

## نقاط النهاية المتاحة

### الإحصائيات العامة
- **URL**: `/api/admin/dashboard/general-stats`
- **Method**: `GET`
- **الوصف**: يوفر إحصائيات عامة عن المنصة مثل عدد الطلاب والمدرسين والكورسات والإيرادات.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "total_students": 150,
      "total_instructors": 25,
      "total_courses": 75,
      "accepted_courses": 60,
      "pending_courses": 15,
      "total_revenue": 15000,
      "platform_revenue": 4500,
      "average_rating": 4.2
    }
  }
  ```

### إحصائيات الإيرادات
- **URL**: `/api/admin/dashboard/revenue-stats`
- **Method**: `GET`
- **المعلمات**:
  - `period` (اختياري): الفترة الزمنية للإحصائيات (daily, weekly, monthly, yearly). الافتراضي: monthly
- **الوصف**: يوفر إحصائيات الإيرادات حسب الفترة الزمنية المحددة.
- **الاستجابة**: (مثال لفترة شهرية)
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "period": "monthly",
      "total_revenue": [
        { "period": "2023-01", "total": 1200 },
        { "period": "2023-02", "total": 1500 },
        ...
      ],
      "platform_revenue": [
        { "period": "2023-01", "total": 360 },
        { "period": "2023-02", "total": 450 },
        ...
      ]
    }
  }
  ```

### تقييمات الكورسات
- **URL**: `/api/admin/dashboard/course-ratings`
- **Method**: `GET`
- **الوصف**: يوفر إحصائيات حول تقييمات الكورسات.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "top_rated_courses": [
        { "id": 5, "title": "كورس البرمجة بلغة PHP", "average_rating": 4.8, "ratings_count": 25 },
        ...
      ],
      "rating_distribution": {
        "1": 5,
        "2": 10,
        "3": 30,
        "4": 100,
        "5": 200
      },
      "ratings_by_category": [
        { "category": "البرمجة", "average_rating": 4.5 },
        ...
      ]
    }
  }
  ```

### إحصائيات المستخدمين
- **URL**: `/api/admin/dashboard/user-stats`
- **Method**: `GET`
- **الوصف**: يوفر إحصائيات حول المستخدمين.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "new_users_by_month": [
        { "month": "2023-01", "count": 15 },
        ...
      ],
      "users_by_role": {
        "admin": 3,
        "instructor": 25,
        "student": 150
      },
      "most_active_students": [
        { "id": 10, "name": "أحمد محمد", "courses_count": 8 },
        ...
      ],
      "most_active_instructors": [
        { "id": 5, "name": "محمد علي", "courses_count": 12 },
        ...
      ]
    }
  }
  ```

### إحصائيات الكورسات
- **URL**: `/api/admin/dashboard/course-stats`
- **Method**: `GET`
- **الوصف**: يوفر إحصائيات حول الكورسات.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": {
      "new_courses_by_month": [
        { "month": "2023-01", "count": 5 },
        ...
      ],
      "courses_by_category": [
        { "category": "البرمجة", "count": 30 },
        ...
      ],
      "top_selling_courses": [
        { "id": 8, "title": "تعلم JavaScript", "students_count": 45 },
        ...
      ],
      "average_lessons_per_course": 12.5
    }
  }
  ```

### أحدث المعاملات المالية
- **URL**: `/api/admin/dashboard/latest-transactions`
- **Method**: `GET`
- **الوصف**: يوفر قائمة بأحدث المعاملات المالية.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "id": 123,
        "amount": 100,
        "date": "2023-05-15T10:30:00Z",
        "from": { "id": 10, "name": "أحمد محمد" },
        "to": { "id": 5, "name": "محمد علي" },
        "course": { "id": 8, "title": "تعلم JavaScript" }
      },
      ...
    ]
  }
  ```

### أحدث المستخدمين المسجلين
- **URL**: `/api/admin/dashboard/latest-users`
- **Method**: `GET`
- **الوصف**: يوفر قائمة بأحدث المستخدمين المسجلين.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "id": 150,
        "name": "سارة أحمد",
        "email": "sara@example.com",
        "profile_picture": "/storage/profile/image.jpg",
        "role": "student",
        "created_at": "2023-05-15T10:30:00Z"
      },
      ...
    ]
  }
  ```

### أحدث الكورسات المضافة
- **URL**: `/api/admin/dashboard/latest-courses`
- **Method**: `GET`
- **الوصف**: يوفر قائمة بأحدث الكورسات المضافة.
- **الاستجابة**:
  ```json
  {
    "status": "success",
    "message": "Done Successfully!",
    "data": [
      {
        "id": 75,
        "title": "مقدمة في الذكاء الاصطناعي",
        "price": 299,
        "status": "accepted",
        "created_at": "2023-05-10T14:20:00Z",
        "instructor": { "id": 8, "name": "خالد العمري" },
        "category": { "id": 3, "name": "علوم الحاسب" }
      },
      ...
    ]
  }
  ```
