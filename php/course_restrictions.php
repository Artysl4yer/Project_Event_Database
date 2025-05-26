<?php
// Define course restrictions for each organization
$COURSE_RESTRICTIONS = [
    'Open for All' => [], // Empty array means all courses are allowed
    'College of Computer Studies (BSIT, BSCS)' => ['BSIT', 'BSCS'],
    'College of Education (BSEDUC)' => ['BSEDUC'],
    'College of Nursing (BSN)' => ['BSN'],
    'College of International Hospitality Management (BSHM)' => ['BSHM'],
    'College of Business Administration (BSBA)' => ['BSBA'],
    'College of Arts and Sciences' => ['BSAS'], 
    'College of Engineering (BSENG)' => ['BSENG']
];

// Function to check if a student's course is allowed for an event
function isCourseAllowed($organization, $studentCourse) {
    global $COURSE_RESTRICTIONS;
    
    // Debug logging
    error_log("DEBUG - isCourseAllowed function called");
    error_log("DEBUG - Organization received: '" . $organization . "'");
    error_log("DEBUG - Student course received: '" . $studentCourse . "'");
    error_log("DEBUG - Available organizations: " . print_r(array_keys($COURSE_RESTRICTIONS), true));
    
    // Trim any whitespace and convert to uppercase for comparison
    $organization = trim($organization);
    $studentCourse = strtoupper(trim($studentCourse));
    
    // If organization is not found in restrictions, deny access
    if (!isset($COURSE_RESTRICTIONS[$organization])) {
        error_log("DEBUG - Organization '$organization' not found in restrictions");
        error_log("DEBUG - Available organizations are: " . implode(", ", array_keys($COURSE_RESTRICTIONS)));
        return false;
    }
    
    // If organization is "Open for All" or has empty restrictions array, allow all courses
    if ($organization === 'Open for All' || empty($COURSE_RESTRICTIONS[$organization])) {
        error_log("DEBUG - Organization '$organization' allows all courses");
        return true;
    }
    
    // Check if student's course is in the allowed courses list
    $allowedCourses = array_map('strtoupper', $COURSE_RESTRICTIONS[$organization]);
    error_log("DEBUG - Allowed courses for '$organization': " . implode(", ", $allowedCourses));
    $isAllowed = in_array($studentCourse, $allowedCourses);
    error_log("DEBUG - Course '$studentCourse' " . ($isAllowed ? "is" : "is not") . " allowed for organization '$organization'");
    
    return $isAllowed;
}
?> 