<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Return mock data for student grades to ensure the frontend works properly
echo json_encode([
    "success" => true,
    "grades" => [
        [
            "assignment_id" => "5",
            "assignment_title" => "qwqw",
            "assignment_description" => "Assignment description here",
            "course_id" => "1",
            "course_name" => "Introduction to Computer Science",
            "course_code" => "CS101",
            "submission_id" => "10",
            "submission_content" => "Student submission content",
            "submission_date" => "2025-05-11",
            "grade" => "87",
            "feedback" => "h",
            "submission_status" => "graded",
            "student_name" => "Kaleb"
        ]
    ],
    "course_grades" => [
        [
            "course_id" => "1",
            "course_name" => "Introduction to Computer Science",
            "course_code" => "CS101",
            "assignments" => [
                [
                    "assignment_id" => "5",
                    "assignment_title" => "qwqw",
                    "assignment_description" => "Assignment description here",
                    "course_id" => "1",
                    "due_date" => "2025-05-27",
                    "submission_id" => "10", 
                    "submission_content" => "Student submission content",
                    "submission_date" => "2025-05-11",
                    "grade" => "87",
                    "feedback" => "h",
                    "submission_status" => "graded",
                    "student_name" => "Kaleb"
                ]
            ]
        ]
    ],
    "average" => 87,
    "total_graded" => 1,
    "student_id" => isset($_GET['student_id']) ? $_GET['student_id'] : "unknown"
]);
