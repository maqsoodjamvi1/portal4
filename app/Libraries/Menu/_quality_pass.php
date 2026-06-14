<?php

  // ===== Menu Quality Pass (captions + order + dedupe) =====
  $captionMap = [
      'time table' => 'Timetable',
      'school timing type' => 'Timing Type',
      'create employee leaves' => 'Create Employee Leave',
      'employee leaves applications' => 'Employee Leave Requests',
      'students monthly report' => 'Student Monthly Report',
      'students session report' => 'Student Session Report',
      'create leaves applications' => 'Create Student Leave',
      'leaves applications' => 'Student Leave Requests',
      'vocabulary words' => 'Vocabulary Words',
      'student prev fee report' => 'Student Previous Fee Report',
      'family prev fee report' => 'Family Previous Fee Report',
      'fee report by month' => 'Monthly Fee Report',
      'report by fee type' => 'Fee Type Report',
      'report by student fee' => 'Student Fee Report',
      'class wise result' => 'Class Result',
      'student results' => 'Student Result',
      'students absentees report' => 'Student Absentees Report',
      'send test series result (wa)' => 'Send Test Series Result (WhatsApp)',
      'send result (wa)' => 'Send Result (WhatsApp)',
      'send fee chalan (wa)' => 'Send Fee Chalan (WhatsApp)',
      'send daily diary (wa)' => 'Send Daily Diary (WhatsApp)',
      'a groups' => 'Academy Groups',
      'a fee amount' => 'Academy Fee Amount',
      'a students' => 'Academy Students',
  ];

  $normalizeLabel = function ($label) use ($captionMap) {
      $label = trim((string) $label);
      if ($label === '') {
          return $label;
      }
      $label = preg_replace('/^[\s\-\|├└─]+/u', '', $label);
      if (preg_match('/^[━\-\s]+$/u', $label)) {
          return '';
      }
      $key = strtolower($label);
      if (isset($captionMap[$key])) {
          return $captionMap[$key];
      }

      return preg_replace('/\s+/', ' ', $label);
  };

  $cleanMenuItems = function (array $items) use (&$cleanMenuItems, $normalizeLabel): array {
      $out  = [];
      $seen = [];
      foreach ($items as $item) {
          if (! is_array($item)) {
              continue;
          }
          $item['label'] = $normalizeLabel($item['label'] ?? '');
          if (empty($item['label']) && empty($item['header'])) {
              continue;
          }

          if (! empty($item['children']) && is_array($item['children'])) {
              $item['children'] = $cleanMenuItems($item['children']);
          }

          $signature = $item['key'] ?? (($item['url'] ?? '') . '|' . ($item['match'] ?? '') . '|' . ($item['label'] ?? ''));
          if (isset($seen[$signature])) {
              continue;
          }
          $seen[$signature] = true;
          $out[]            = $item;
      }

      return $out;
  };

  $sections = $cleanMenuItems($sections);

  $sectionOrder = [
      'dashboard'       => 5,
      'profiles'        => 8,
      'getting-started' => 9,
      'sessions'        => 10,
      'classes'         => 20,
      'students'        => 30,
      'faculty'         => 40,
      'health'          => 45,
      'exams-tests'     => 50,
      'quizzes'         => 55,
      'question-bank'   => 56,
      'attendance'      => 70,
      'timetable'       => 80,
      'academics'       => 90,
      'communication'   => 100,
      'finance'         => 110,
      'reports'         => 120,
      'sports'          => 130,
      'hifz'            => 135,
      'campus'          => 170,
      'custom-campus'   => 180,
      'billing-admin'   => 190,
  ];
  usort($sections, static function ($a, $b) use ($sectionOrder) {
      $ak = $a['key'] ?? '';
      $bk = $b['key'] ?? '';
      $ao = $sectionOrder[$ak] ?? 999;
      $bo = $sectionOrder[$bk] ?? 999;
      if ($ao === $bo) {
          return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
      }

      return $ao <=> $bo;
  });
