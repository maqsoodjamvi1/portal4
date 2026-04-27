<!DOCTYPE html>
<html>
<head>
    <title>Teacher Timetable</title>
    <style>
        .timetable {
            width: 100%;
            border-collapse: collapse;
        }
        .timetable th, .timetable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .timetable th {
            background-color: #f2f2f2;
        }
        .day-header {
            background-color: #e6f7ff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Teacher Timetable</h1>
    
    <select id="teacherSelect">
        <option value="">All Teachers</option>
        <?php foreach ($teachers as $teacher): ?>
            <option value="<?= $teacher['tid'] ?>"><?= $teacher['name'] ?></option>
        <?php endforeach; ?>
    </select>
    
    <button onclick="loadTimetable()">Show Timetable</button>
    
    <div id="timetableContainer">
        <!-- Timetable will be loaded here -->
    </div>

    <script>
        function loadTimetable() {
            const teacherId = document.getElementById('teacherSelect').value;
            fetch(`/timetable/teacher?teacher_id=${teacherId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTimetable(data.data);
                    }
                });
        }

        function renderTimetable(data) {
            const container = document.getElementById('timetableContainer');
            container.innerHTML = '';

            if (!data || Object.keys(data).length === 0) {
                container.innerHTML = '<p>No classes scheduled for this teacher.</p>';
                return;
            }

            for (const [day, slots] of Object.entries(data)) {
                const dayDiv = document.createElement('div');
                dayDiv.innerHTML = `
                    <h2 class="day-header">${day}</h2>
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${slots.map(slot => `
                                <tr>
                                    <td>${slot.start_time} - ${slot.end_time}</td>
                                    <td>${slot.class_name}</td>
                                    <td>${slot.section_name}</td>
                                    <td>${slot.subject_name}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
                container.appendChild(dayDiv);
            }
        }

        // Load timetable for the first teacher by default
        document.addEventListener('DOMContentLoaded', loadTimetable);
    </script>
</body>
</html>