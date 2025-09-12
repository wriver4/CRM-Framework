<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
require_once '../classes/Models/Leads.php';

// Direct routing variables - these determine page navigation and template inclusion
$page = 'dashboard';

// Initialize Leads class and get dashboard data
$leads = new Leads();
$dashboardData = $leads->getDashboardSummary();

// Load language file for multilingual support
$lang = include 'admin/languages/en.php';
?>

<!-- Dashboard Analytics with Apache ECharts -->
<div class="row mb-4">
    <!-- Summary Cards -->
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $dashboardData['total_leads']; ?></h4>
                        <p class="card-text">Total Leads</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $dashboardData['active_leads']; ?></h4>
                        <p class="card-text">Active Leads</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $dashboardData['closed_won']; ?></h4>
                        <p class="card-text">Closed Won</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-trophy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $dashboardData['conversion_rate']; ?>%</h4>
                        <p class="card-text">Conversion Rate</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Lead Stages Pie Chart -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Leads by Stage
                </h5>
            </div>
            <div class="card-body">
                <div id="stagesPieChart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Lead Sources Donut Chart -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-donut me-2"></i>
                    Leads by Source
                </h5>
            </div>
            <div class="card-body">
                <div id="sourcesDonutChart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Trend and Recent Activity Row -->
<div class="row mb-4">
    <!-- Lead Trend Line Chart -->
    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Lead Trend (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <div id="leadTrendChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Recent Leads -->
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($dashboardData['recent_leads'] as $lead): ?>
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($lead['full_name'] ?? 'No Name'); ?></h6>
                                <p class="mb-1 text-muted small"><?php echo htmlspecialchars($lead['email']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($lead['updated_at'])); ?>
                                </small>
                            </div>
                            <span class="<?php echo $lead['badge_class']; ?> ms-2">
                                <?php echo $lead['stage_name']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($dashboardData['recent_leads'])): ?>
                    <div class="list-group-item px-0 py-3 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No recent activity</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="/leads/list.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-list me-1"></i>
                        View All Leads
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stage Breakdown Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>
                    Stage Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Stage</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboardData['stage_counts'] as $stage): ?>
                            <?php 
                                $percentage = $dashboardData['total_leads'] > 0 
                                    ? round(($stage['count'] / $dashboardData['total_leads']) * 100, 1) 
                                    : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="<?php echo $stage['badge_class']; ?>">
                                        <?php echo $stage['stage_name']; ?>
                                    </span>
                                </td>
                                <td><strong><?php echo $stage['count']; ?></strong></td>
                                <td><?php echo $percentage; ?>%</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apache ECharts JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
// Dashboard data from PHP
const dashboardData = <?php echo json_encode($dashboardData); ?>;

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeStagesPieChart();
    initializeSourcesDonutChart();
    initializeLeadTrendChart();
});

// Lead Stages Pie Chart
function initializeStagesPieChart() {
    const chartDom = document.getElementById('stagesPieChart');
    const myChart = echarts.init(chartDom);
    
    const stageData = dashboardData.stage_counts.map(stage => ({
        name: stage.stage_name,
        value: stage.count
    }));
    
    const option = {
        title: {
            text: 'Lead Distribution by Stage',
            left: 'center',
            textStyle: {
                fontSize: 16,
                fontWeight: 'normal'
            }
        },
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            top: 'middle'
        },
        series: [
            {
                name: 'Leads',
                type: 'pie',
                radius: ['0%', '70%'],
                center: ['60%', '50%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: stageData,
                color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc']
            }
        ]
    };
    
    myChart.setOption(option);
    
    // Make chart responsive
    window.addEventListener('resize', function() {
        myChart.resize();
    });
}

// Lead Sources Donut Chart
function initializeSourcesDonutChart() {
    const chartDom = document.getElementById('sourcesDonutChart');
    const myChart = echarts.init(chartDom);
    
    const sourceData = dashboardData.source_counts.map(source => ({
        name: source.source_name,
        value: source.count
    }));
    
    const option = {
        title: {
            text: 'Lead Sources',
            left: 'center',
            textStyle: {
                fontSize: 16,
                fontWeight: 'normal'
            }
        },
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            top: 'middle'
        },
        series: [
            {
                name: 'Sources',
                type: 'pie',
                radius: ['40%', '70%'],
                center: ['60%', '50%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: true,
                    position: 'outside',
                    formatter: '{b}: {c}'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                },
                data: sourceData,
                color: ['#ff7f50', '#87ceeb', '#da70d6', '#32cd32', '#6495ed', '#ff69b4']
            }
        ]
    };
    
    myChart.setOption(option);
    
    // Make chart responsive
    window.addEventListener('resize', function() {
        myChart.resize();
    });
}

// Lead Trend Line Chart
function initializeLeadTrendChart() {
    const chartDom = document.getElementById('leadTrendChart');
    const myChart = echarts.init(chartDom);
    
    const dates = dashboardData.daily_counts.map(item => item.date);
    const counts = dashboardData.daily_counts.map(item => item.count);
    
    const option = {
        title: {
            text: 'Daily Lead Creation Trend',
            left: 'center',
            textStyle: {
                fontSize: 16,
                fontWeight: 'normal'
            }
        },
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                const date = new Date(params[0].axisValue).toLocaleDateString();
                return `${date}<br/>Leads Created: ${params[0].value}`;
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: dates,
            axisLabel: {
                formatter: function(value) {
                    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }
        },
        yAxis: {
            type: 'value',
            minInterval: 1
        },
        series: [
            {
                name: 'Leads Created',
                type: 'line',
                stack: 'Total',
                smooth: true,
                lineStyle: {
                    width: 3
                },
                areaStyle: {
                    opacity: 0.3
                },
                data: counts,
                itemStyle: {
                    color: '#5470c6'
                }
            }
        ]
    };
    
    myChart.setOption(option);
    
    // Make chart responsive
    window.addEventListener('resize', function() {
        myChart.resize();
    });
}
</script>