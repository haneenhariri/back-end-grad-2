<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            [
                'mainCategory' => ['en' => 'Development', 'ar' => 'التطوير'],
                'subCategory' => [
                    [
                        'en' => 'Front-End Development',
                        'ar' => 'تطوير الواجهة الأمامية'
                    ],
                    [
                        'en' => 'Back-End Development',
                        'ar' => 'تطوير الواجهة الخلفية'
                    ],
                    [
                        'en' => 'Full-Stack Development',
                        'ar' => 'تطوير البرمجيات الكاملة'
                    ],
                    [
                        'en' => 'Mobile App Development',
                        'ar' => 'تطوير تطبيقات موبايل'
                    ],
                    [
                        'en' => 'Game Development',
                        'ar' => 'تطوير الألعاب'
                    ],
                ]
            ],
            [
                'mainCategory' => [
                    'en' => 'AI & ML',
                    'ar' => 'الذكاء الاصطناعي والتعلم الآلي'
                ],
                'subCategory' => [
                    [
                        'en' => 'Machine Learning',
                        'ar' => 'التعلم الآلي'
                    ],
                    [
                        'en' => 'Deep Learning',
                        'ar' => 'التعلم العميق'
                    ],
                    [
                        'en' => 'Natural Language Processing (NLP)',
                        'ar' => 'معالجة اللغات الطبيعية (NLP)'
                    ],
                    [
                        'en' => 'Computer Vision',
                        'ar' => 'الرؤية الحاسوبية'
                    ],
                    [
                        'en' => 'AI for Business',
                        'ar' => 'الذكاء الاصطناعي للأعمال'
                    ],
                ]
            ],
            [
                'mainCategory' => [
                    'en' => 'Data Science',
                    'ar' => 'علم البيانات'
                ],
                'subCategory' => [
                    [
                        'en' => 'Data Analysis',
                        'ar' => 'تحليل البيانات'
                    ],
                    [
                        'en' => 'Data Visualization',
                        'ar' => 'تصور البيانات'
                    ],
                    [
                        'en' => 'Big Data Analytics',
                        'ar' => 'تحليل البيانات الضخمة'
                    ],
                    [
                        'en' => 'Data Engineering',
                        'ar' => 'هندسة البيانات'
                    ],
                    [
                        'en' => 'Data Mining',
                        'ar' => 'استخراج البيانات'
                    ],
                    [
                        'en' => 'Business Intelligence (BI)',
                        'ar' => 'ذكاء الأعمال (BI)'
                    ],
                    [
                        'en' => 'SQL & NoSQL Databases',
                        'ar' => 'قواعد بيانات SQL وNoSQL'
                    ],

                ]
            ],
            [
                'mainCategory' => [
                    'en' => 'Cyber-security',
                    'ar' => 'الأمن السيبراني'
                ],
                'subCategory' => [
                    [
                        'en' => 'Ethical Hacking & Penetration Testing',
                        'ar' => 'الاختراق الأخلاقي واختبار الاختراق'
                    ],
                    [
                        'en' => 'Network Security',
                        'ar' => 'أمن الشبكات'
                    ],
                    [
                        'en' => 'Cryptography',
                        'ar' => 'التشفير'
                    ],
                    [
                        'en' => 'Cloud Security',
                        'ar' => 'أمن السحابة'
                    ],
                    [
                        'en' => 'Web Security',
                        'ar' => 'أمن الويب'
                    ],

                ]
            ],
            [
                'mainCategory' => [
                    'en' => 'Design',
                    'ar' => 'التصميم'
                ],
                'subCategory' => [
                    [
                        'en' => 'UI/UX Design',
                        'ar' => 'تصميم واجهة المستخدم وتجربة المستخدم'
                    ],
                    [
                        'en' => 'Graphic Design',
                        'ar' => 'التصميم الجرافيكي'
                    ],
                    [
                        'en' => '3D Modeling & Animation',
                        'ar' => 'النمذجة والرسوم المتحركة ثلاثية الأبعاد'
                    ],
                    [
                        'en' => 'Motion Graphics',
                        'ar' => 'الرسوم المتحركة'
                    ],
                    [
                        'en' => 'Branding & Identity Design',
                        'ar' => 'تصميم العلامات التجارية والهويات التجارية'
                    ],
                    [
                        'en' => 'Product Design',
                        'ar' => 'تصميم المنتجات'
                    ],

                ]
            ],
            [
                'mainCategory' => [
                    'en' => 'IT & Software',
                    'ar' => 'تكنولوجيا المعلومات والبرمجيات'
                ],
                'subCategory' => [
                    [
                        'en' => 'IT Support & Help Desk',
                        'ar' => 'دعم تكنولوجيا المعلومات وخدمة العملاء'
                    ],
                    [
                        'en' => 'Operating Systems',
                        'ar' => 'أنظمة التشغيل'
                    ],
                    [
                        'en' => 'Networking',
                        'ar' => 'الشبكات'
                    ],
                    [
                        'en' => 'Database Management',
                        'ar' => 'إدارة قواعد البيانات'
                    ],
                    [
                        'en' => 'Software Testing',
                        'ar' => 'اختبار البرمجيات'
                    ],
                    [
                        'en' => 'IT Project Management',
                        'ar' => 'إدارة مشاريع تكنولوجيا المعلومات'
                    ],
                    [
                        'en' => 'Enterprise Resource Planning (ERP)',
                        'ar' => 'تخطيط موارد المؤسسة (ERP)'
                    ],

                ]
            ]

        ];
        foreach ($categories as $category) {
            $mainCategory = Category::create(['name'=>$category['mainCategory']]);
            foreach ($category['subCategory'] as $subCategory){
                Category::create([
                    'category_id'=>$mainCategory->id,
                    'name'=>$subCategory
                ]);
            }
        }
    }
}
