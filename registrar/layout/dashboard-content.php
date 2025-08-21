<?php
// Dashboard content
?>
<section class="section main-section">
    <div class="dashboard-container">
        <!-- Statistics Cards -->
        <div class="stat-card">
            <div class="stat-value"><?php echo $buildings_count; ?></div>
            <div class="stat-label">Total Buildings</div>
            <div class="stat-icon">
                <i class="mdi mdi-office-building"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $rooms_count; ?></div>
            <div class="stat-label">Total Rooms</div>
            <div class="stat-icon">
                <i class="mdi mdi-door"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $equipment_count; ?></div>
            <div class="stat-label">Total Equipment</div>
            <div class="stat-icon">
                <i class="mdi mdi-desktop-mac"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $department_count; ?></div>
            <div class="stat-label">Total Departments</div>
            <div class="stat-icon">
                <i class="mdi mdi-domain"></i>
            </div>
        </div>

        <!-- Department Statistics -->
        <div class="chart-card chart-card-full">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-domain"></i></span>
                    Department Facilities Overview
                </h3>
            </div>
            <div class="card-content table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Buildings</th>
                            <th>Rooms</th>
                            <th>Total Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department_stats as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['department']); ?></td>
                            <td><?php echo $dept['building_count']; ?></td>
                            <td><?php echo $dept['room_count']; ?></td>
                            <td><?php echo $dept['total_capacity']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($department_stats)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No department data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Charts: Room Status and Equipment Status -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                    Room Status Distribution
                </h3>
            </div>
            <div class="card-content">
                <canvas id="roomStatusChart" height="300"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                    Equipment Status Distribution
                </h3>
            </div>
            <div class="card-content">
                <canvas id="equipmentStatusChart" height="300"></canvas>
            </div>
        </div>

        <!-- Room Types Distribution Chart -->
        <div class="chart-card chart-card-full">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-view-grid"></i></span>
                    Room Types Distribution
                </h3>
            </div>
            <div class="card-content">
                <canvas id="roomTypesChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Largest Buildings by Room Count -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-office-building"></i></span>
                    Largest Buildings
                </h3>
            </div>
            <div class="card-content table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Department</th>
                            <th>Rooms</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($largest_buildings as $building): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($building['building_name']); ?></td>
                            <td><?php echo htmlspecialchars($building['department']); ?></td>
                            <td><?php echo $building['room_count']; ?></td>
                            <td><?php echo $building['total_capacity']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($largest_buildings)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Rooms with Highest Capacity -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-seat"></i></span>
                    Highest Capacity Rooms
                </h3>
            </div>
            <div class="card-content table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Building</th>
                            <th>Department</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($high_capacity_rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                            <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                            <td><?php echo htmlspecialchars($room['department']); ?></td>
                            <td><?php echo $room['capacity']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($high_capacity_rooms)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Equipment Issues -->
        <div class="chart-card chart-card-full">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-tools"></i></span>
                    Recent Equipment Issues
                </h3>
            </div>
            <div class="card-content">
                <?php foreach ($recent_issues as $issue): ?>
                <div class="issue-item">
                    <div class="issue-title">
                        <?php echo htmlspecialchars($issue['equipment_name']); ?> - 
                        <?php echo htmlspecialchars($issue['issue_type']); ?>
                    </div>
                    <div class="issue-meta">
                        <div>
                            <strong>Location:</strong> <?php echo htmlspecialchars($issue['room_name']); ?>, 
                            <?php echo htmlspecialchars($issue['building_name']); ?> 
                            (<?php echo htmlspecialchars($issue['department']); ?>)
                        </div>
                        <div>
                            <strong>Reported:</strong> <?php echo date('M d, Y', strtotime($issue['reported_at'])); ?>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo strtolower($issue['status']); ?>">
                                <?php echo ucfirst($issue['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recent_issues)): ?>
                <div class="issue-item">
                    <div class="issue-title">No recent equipment issues found</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Department Room Distribution -->
        <div class="chart-card chart-card-full">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon"><i class="mdi mdi-chart-bar"></i></span>
                    Rooms by Department
                </h3>
            </div>
            <div class="card-content">
                <canvas id="roomsByDepartmentChart" height="100"></canvas>
            </div>
        </div>
    </div>
</section>
