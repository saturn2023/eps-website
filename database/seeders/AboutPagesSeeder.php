<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The four About pages, with content from the review prototype
 * (images in database/seeders/assets/prototype). Only creates pages
 * that don't exist yet, so it never overwrites edits made in the admin.
 */
class AboutPagesSeeder extends Seeder
{
    private const SRC = 'seeders/assets/prototype/images/';

    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }
    }

    /** Copy a prototype image into the public disk and return its stored path. */
    private function image(string $file, string $folder): string
    {
        $path = $folder . '/' . basename($file);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path(self::SRC . $file)));
        }

        return $path;
    }

    private function pages(): array
    {
        return [
            $this->ourSchool(),
            $this->principalsWelcome(),
            $this->visionValues(),
            $this->schoolBoard(),
        ];
    }

    private function ourSchool(): array
    {
        return [
            'title' => 'Our School',
            'slug' => 'our-school',
            'section' => 'about',
            'sort' => 1,
            'heading' => 'Ellenbrook Primary School History',
            'intro' => 'From around 50 students in 1996 to a thriving Independent Public School at the heart of the Ellenbrook community.',
            'icon' => $this->image('icons/our-school.png', 'pages/icons'),
            'seo_description' => 'The history of Ellenbrook Primary School, from its opening in 1996 as the first school in houses in WA to an Independent Public School today.',
            'blocks' => [
                ['type' => 'text_timeline', 'data' => [
                    'body' => '<h2>Where it all began</h2><p>Established in 1996, Ellenbrook Primary School was the first school in houses in Western Australia. The school opened its doors with approximately 50 students and moved to its current location on Fortescue Place at the commencement of 1997.</p><p>Since these early days our student numbers have swelled to 740 in 2020, however with the opening of a new school in Brabham our numbers now stand at 473.</p><h2>An Independent Public School</h2><p>Ellenbrook Primary School became an Independent Public School in 2011. This meant that the school could change according to the needs and expectations of the community.</p><p>It gave more freedom and flexibility for the school community to be involved with decisions about important matters that impact on the student\'s education.</p>',
                    'timeline_heading' => 'Our journey so far',
                    'milestones' => [
                        ['year' => '1996', 'text' => 'Opened as the first school in houses in WA, with around 50 students.', 'highlight' => false],
                        ['year' => '1997', 'text' => 'Moved to our current home on Fortescue Place.', 'highlight' => false],
                        ['year' => '2011', 'text' => 'Became an Independent Public School.', 'highlight' => false],
                        ['year' => '2020', 'text' => 'Student numbers reached 740.', 'highlight' => false],
                        ['year' => 'Today', 'text' => '473 students, following the opening of a new school in Brabham.', 'highlight' => true],
                    ],
                ]],
                ['type' => 'phrase', 'data' => [
                    'phrase' => 'Preparing our students for life-long learning.',
                    'artwork' => $this->image('bird-white.webp', 'pages/art'),
                ]],
                ['type' => 'statement_chips', 'data' => [
                    'heading' => 'Our goal',
                    'text' => 'The goal of Ellenbrook Primary School is to prepare our students for life-long learning by providing a positive, safe, caring and cooperative environment that promotes these qualities among students, staff and parents:',
                    'chips' => ['Equality', 'Respect', 'Creativity', 'Self-esteem', 'The pursuit of excellence'],
                ]],
            ],
        ];
    }

    private function principalsWelcome(): array
    {
        $points = fn (array $items) => '<ol>' . implode('', array_map(fn ($i) => '<li><p>' . e($i) . '</p></li>', $items)) . '</ol>';

        return [
            'title' => "Principal's Welcome",
            'slug' => 'principals-welcome',
            'section' => 'about',
            'sort' => 2,
            'heading' => null,
            'intro' => null,
            'icon' => $this->image('icons/principals-welcome.png', 'pages/icons'),
            'seo_description' => 'A welcome from Ellenbrook Primary School principal Tony Shields on building strong relationships, high standards and focused, joyful learning.',
            'blocks' => [
                ['type' => 'text', 'data' => [
                    'lead' => 'Ellenbrook Primary School is different, and that difference is based on how we respond to parents\' desires to make certain that their children get a first-class education that will optimise learning opportunities.',
                    'numbered_cards' => true,
                    'body' => '<h2>Our operational style</h2>' . $points([
                        'We get the relationships right. We need the students to develop happy learning relationships with staff, and for parents to feel welcome in the school.',
                        'We ensure our learning program will be focussed, of a high standard, and joyful.',
                        'We make certain our students with different needs are accommodated, within the school\'s capability. We are very aware of the achievement gap and the opportunity gaps that affect our students and we work hard to bridge those gaps.',
                        'We act to make certain that students who want to learn can do this without unnecessary interruption. Misbehaving students receive controlled counselling.',
                        'We believe that the moral purpose of schooling is to develop good, happy and productive citizens.',
                    ]) . '<h2>Just a couple of clarifying points</h2>' . $points([
                        'We work hard on developing a calm, caring environment in which everyone has equal rights. The principal and deputies are always available in the school grounds before and after school, and parents use this to solve minor problems before they become big issues. Teaching staff cannot be disturbed while they are teaching, so parents will need to make appointments.',
                        'We push for the students to achieve high standards. We require high standards of presentation, and all students are required to do their best work.',
                        'We are a part of the Teaching Action Group schools (Dayton and Brabham), which is developing programs based on research, to maximise students\' learning.',
                        'Staff at our school are driven and highly motivated to make the most positive impact on student learning through strong collaboration and a Coaching model that continually improves what we do.',
                        'Our school gets visitors from other schools in Australia, who come in to see how staff work to get such good results.',
                    ]) . '<h2>Business Plan 2023 to 2027</h2><p>As a school, we think that the future is promising for our school and our students.</p>',
                    'downloads' => [
                        ['label' => 'Business Plan 2023 to 2027', 'note' => 'PDF to be uploaded', 'file' => null, 'url' => null],
                    ],
                    'signature_name' => 'Mr Anthony (Tony) Shields',
                    'signature_role' => 'BEd., Principal',
                    'photo' => null,
                    'photo_alt' => 'Mr Tony Shields, Principal',
                    'photo_caption' => null,
                    'photo_placeholder' => 'Photo of Mr Tony Shields to be supplied',
                ]],
            ],
        ];
    }

    private function visionValues(): array
    {
        $given = fn (string $title, ?string $summary, array $points) => compact('title', 'summary', 'points');

        return [
            'title' => 'Our Vision and Values',
            'slug' => 'vision-values',
            'section' => 'about',
            'sort' => 3,
            'heading' => null,
            'intro' => 'What we believe, what we value, and what you will see in every classroom.',
            'icon' => $this->image('icons/vision-values.png', 'pages/icons'),
            'seo_description' => 'Our vision, our REAL school values of Respect, Resilience and Active Learning, and the six givens that shape every classroom at Ellenbrook Primary School.',
            'blocks' => [
                ['type' => 'vision', 'data' => [
                    'label' => 'Our vision',
                    'statement' => '<p>At Ellenbrook Primary School, we inspire a community where <strong>Respect, Resilience and Active Learning</strong> are the foundation of every student\'s success. Together, we foster a safe, inclusive environment that prepares our students to thrive in an evolving world.</p>',
                    'image' => $this->image('real-values.png', 'pages/images'),
                    'image_alt' => 'REAL: Respect, Resilience, Active Learner, with the school bee characters',
                ]],
                ['type' => 'value_cards', 'data' => [
                    'heading' => 'Our school values',
                    'intro' => 'Our values spell REAL. Students learn them through our bee characters, and they guide how we learn and treat each other every day.',
                    'cards' => [
                        ['title' => 'Respect', 'letters' => 'R', 'colour' => '#2776BB', 'text' => 'We treat everyone kindly and listen carefully.', 'image' => $this->image('bee-respect.png', 'pages/values'), 'image_alt' => 'Respect bee character'],
                        ['title' => 'Resilience', 'letters' => 'E', 'colour' => '#D63A63', 'text' => 'We bounce back and grow through challenges.', 'image' => $this->image('bee-resilience.png', 'pages/values'), 'image_alt' => 'Resilience bee character'],
                        ['title' => 'Active Learner', 'letters' => 'AL', 'colour' => '#3A9A48', 'text' => 'We engage with material through discussion and practice.', 'image' => $this->image('bee-active-learner.png', 'pages/values'), 'image_alt' => 'Active Learner bee character'],
                    ],
                ]],
                ['type' => 'phrase', 'data' => [
                    'phrase' => 'Together, we foster a safe, inclusive environment.',
                    'artwork' => $this->image('bird-white.webp', 'pages/art'),
                ]],
                ['type' => 'numbered_cards', 'data' => [
                    'heading' => 'Our school givens',
                    'intro' => 'At Ellenbrook Primary School, our learning environment is defined by six givens that shape each classroom.',
                    'items' => [
                        $given('Strong Relationships', 'Building strong relationships is essential; students need to feel they are trusted and supported by teachers who believe in them.', [
                            'Model respect, courtesy and honesty, greeting students and families daily.',
                            'Show enthusiasm and resilience, guiding students to see mistakes as learning opportunities.',
                        ]),
                        $given('Positive Classroom Tone', null, [
                            'Foster a positive, supportive environment where every student feels secure and motivated.',
                            'Model positive behaviours, ensuring a respectful and engaging learning community.',
                            'Classrooms should have structure, creating a sense of order that supports focus.',
                            'Establish an inclusive space where individual learning needs are recognised and supported.',
                        ]),
                        $given('High Expectations and Excellent Practices', null, [
                            'Set clear and consistent expectations; enforce routines, requiring students to meet standards the first time, every time.',
                            'Set the standard for excellence in work quality, behaviour and classroom etiquette, with high standards for presentation and preparedness.',
                        ]),
                        $given('Pride in Presentation', null, [
                            'Inspire students to take pride in their learning, from the quality of their work to how they present themselves in uniform.',
                            'Encourage students to continuously improve, respecting the learning environment and maintaining neat and organised work.',
                            'Create classrooms that are organised, clean and welcoming, with clearly defined learning areas.',
                            'Showcase a balance of student work and teacher resources in engaging, high-quality displays.',
                        ]),
                        $given('Mastery Through Resilience', null, [
                            'Promote a growth mindset where students learn from challenges and build skills that stick.',
                            'Support students in building resilience by celebrating effort and persistence.',
                            'Encourage students to persevere, learn from mistakes, and transfer learning to long-term memory for deep understanding.',
                        ]),
                        $given('Quality, Timely Feedback', null, [
                            'Provide constructive feedback that drives growth and fosters resilience, reinforcing continuous improvement in learning and skills.',
                        ]),
                    ],
                ]],
            ],
        ];
    }

    private function schoolBoard(): array
    {
        return [
            'title' => 'School Board',
            'slug' => 'school-board',
            'section' => 'about',
            'sort' => 4,
            'heading' => 'Ellenbrook Primary School Board',
            'intro' => null,
            'icon' => $this->image('icons/school-board.png', 'pages/icons'),
            'seo_description' => 'The Ellenbrook Primary School Board supports the strategic direction of the school and connects the school with its community.',
            'blocks' => [
                ['type' => 'text', 'data' => [
                    'lead' => 'The Ellenbrook Primary School Board plays an important role in supporting the strategic direction of our school and strengthening the connection between the school and its community.',
                    'body' => '<p>The Board works collaboratively with the Principal and school leadership to contribute to school planning, monitor progress towards key priorities, and ensure the school remains focused on delivering high-quality outcomes for students. It provides valuable community perspective and helps ensure decisions reflect the needs and aspirations of our families.</p><p>Board members are drawn from our school community and bring a range of skills, experience and perspectives. Together, they support transparent governance and contribute to the ongoing success and continuous improvement of Ellenbrook Primary School.</p><p>We value the contribution of our School Board and the important role it plays in supporting strong educational outcomes for all students.</p>',
                    'numbered_cards' => false,
                    'downloads' => [],
                ]],
                ['type' => 'members', 'data' => [
                    'heading' => 'School Board members',
                    'members' => [
                        ['name' => 'Anthony (Tony) Shields', 'role' => 'Principal'],
                        ['name' => 'Troy Findlay', 'role' => 'Parent Representative, School Board Chair'],
                        ['name' => 'Tarryne Hutchinson', 'role' => 'Parent Representative'],
                        ['name' => 'Matt Rutter', 'role' => 'Parent Representative'],
                        ['name' => 'Nathan Brown', 'role' => 'Ellenbrook Secondary College, Community Representative'],
                        ['name' => 'Greg Pekic', 'role' => 'Office of the Hon Rita Saffioti MLA, Member for West Swan, Community Representative'],
                        ['name' => 'Liz Dolliver', 'role' => 'Staff Representative'],
                    ],
                ]],
            ],
        ];
    }
}
