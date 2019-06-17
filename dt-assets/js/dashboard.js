jQuery(document).ready(function($) {
  let data = wpApiDashboard.data

  // update_needed();

  $('#active_contacts').html(data.active_contacts)
  $('#needs_accepting').html(data.accept_needed_count)
  let needs_accepting_list = ``
  data.accept_needed.contacts.slice(0, 3).forEach( contact =>{
    needs_accepting_list += `<div style="margin-top:10px; display: flex">
        <div style="display: inline-block; vertical-align: middle"><i class="fi-torso large"></i></div>
        <div style="display: inline-block; margin-left: 10px; vertical-align: middle; flex-grow: 1">
            <a style="font-size: 1.3rem" href="${wpApiDashboard.site_url}/contacts/${contact.ID}">${_.escape(
      contact.post_title)}</a>
        </div>
        <div>
            <button class="button small" style="background-color: rgba(76,175,80,0.21); color: black; margin-bottom: 0">Accept</button>
            <button class="button small" style="background-color: rgba(236,17,17,0.2); color: black; margin-bottom: 0">Decline</button>
        </div>
    </div>`
  })
  $('#needs_accepting_list').html( needs_accepting_list )

  /**
   * Update Needed
   */
  $('#update_needed').html(data.update_needed_count)
  let up_list = ``
  data.update_needed.contacts.slice(0, 3).forEach( contact =>{
    let row = `<div style="margin-top:10px">
        <div style="display: inline-block"><i class="fi-torso large"></i></div>
        <div style="display: inline-block; margin-left: 10px">
            <a style="font-size: 1.3rem" href="${wpApiDashboard.site_url}/contacts/${contact.ID}">${_.escape( contact.post_title ) }</a>
            <br>
            <span>44 days since last update<span>
        </div>
    </div>`
    up_list += row
  })
  $('#update_needed_list').html( up_list)
  
  $('#view_updated_needed_button').on( "click", function () {
    document.location = `${wpApiDashboard.site_url}/contacts?list-tab=update_needed`
  })
  $('#view_needs_accepted_button').on( "click", function () {
    document.location = `${wpApiDashboard.site_url}/contacts?list-tab=needs_accepted`
  })





  benchmarks_chart()
  seeker_path_chart()
  milestones()


  function update_needed(){

// Create chart instance
    let chart = am4core.create("chartdiv", am4charts.PieChart);
    let title = chart.titles.create()
    title.text = `[bold] Update Needed [/]`

// Add and configure Series
    let pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "label";
    pieSeries.dataFields.radiusValue = "radius";
    pieSeries.labels.template.disabled = true;
    pieSeries.ticks.template.disabled = true;

    let label = chart.seriesContainer.createChild(am4core.Label);
    label.text =  data.update_needed_count;
    label.horizontalCenter = "middle";
    label.verticalCenter = "middle";
    label.fontSize = 50;


// Let's cut a hole in our Pie chart the size of 30% the radius
    chart.innerRadius = am4core.percent(50);


    pieSeries.slices.template
      // change the cursor on hover to make it apparent the object can be interacted with
      .cursorOverStyle = [
      {
        "property": "cursor",
        "value": "pointer"
      }
    ];

    pieSeries.colors.list = [
      am4core.color("#c20011"),
      am4core.color("#10c200"),
    ];

    pieSeries.alignLabels = false;
    // pieSeries.labels.template.bent = true;
    // pieSeries.labels.template.radius = 3;
    pieSeries.labels.template.padding(0,0,0,0);

    pieSeries.ticks.template.disabled = true;

// Create a base filter effect (as if it's not there) for the hover to return to
    let shadow = pieSeries.slices.template.filters.push(new am4core.DropShadowFilter);
    shadow.opacity = 0;

// Create hover state
    let hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists

// Slightly shift the shadow and make it more prominent on hover
    let hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter);
    hoverShadow.opacity = 0.7;
    hoverShadow.blur = 5;

// Add a legend
//   chart.legend = new am4charts.Legend();

    chart.data = [{
      "label": "Update Needed",
      "value": data.update_needed_count,
      "radius": 200
    },{
      "label": "Good",
      "value": data.active_contacts,
      "radius": 100
    }];


    pieSeries.slices.template.events.on("hit", function(ev) {
      console.log("clicked on ", ev.target);
      let query = {"type":"default","ID":"update_needed","query":{"assigned_to":["me"],"requires_update":[true],"sort":"-last_modified"},"labels":[{"id":"update_needed","name":"Update needed","field":"requires_update"}],"tab":"my"}
      document.cookie = `last_view=${JSON.stringify(query)}`
      document.location = `${wpApiDashboard.site_url}/contacts`
    }, this);
  }

  function benchmarks_chart() {
    let thirty_days_ago = moment().add( -30, "days")
    let sixty_days_ago = moment().add( -60, "days")
    $('#benchmarks_current').html(`${thirty_days_ago.format("MMMM D, YYYY")} to ${moment().format("MMMM D, YYYY")}`)
    $('#benchmarks_previous').html(`${sixty_days_ago.format("MMMM D, YYYY")} to ${thirty_days_ago.format("MMMM D, YYYY")}`)

    am4core.useTheme(am4themes_animated);
    var chart = am4core.create("benchmark_chart", am4charts.XYChart);

    chart.data = [ {
      "year": "# Contacts Assigned",
      "previous": data.benchmarks.contacts.previous,
      "current": data.benchmarks.contacts.current
    }, {
      "year": "# Meetings",
      "previous": data.benchmarks.meetings.previous,
      "current": data.benchmarks.meetings.current
    }, {
      "year": "# Faith milestones",
      "previous": data.benchmarks.milestones.previous,
      "current": data.benchmarks.milestones.current
    } ];

    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "year";
    // categoryAxis.title.text = "Local country offices";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.minGridDistance = 20;
    categoryAxis.renderer.cellStartLocation = 0.1;
    categoryAxis.renderer.cellEndLocation = 0.9;
    categoryAxis.renderer.grid.template.disabled = true;



    var  valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;
    // valueAxis.title.text = "Expenditure (M)";
    valueAxis.renderer.grid.template.disabled = true;
    valueAxis.renderer.labels.template.disabled = true;


    // Create series
    function createSeries(field, name, stacked) {
      var series = chart.series.push(new am4charts.ColumnSeries());
      series.dataFields.valueY = field;
      series.dataFields.categoryX = "year";
      series.name = name;
      series.columns.template.tooltipText = "{name}: [bold]{valueY}[/]";
      series.stacked = stacked;
      series.columns.template.width = am4core.percent(95);
    }
    chart.colors.list = [
      am4core.color("#C7E3FF"),
      am4core.color("#3f729b"),
    ];

    createSeries("previous", "Previous", false);
    createSeries("current", "Current", false);

  }
  
  
  function seeker_path_chart() {
    am4core.useTheme(am4themes_animated);

    var chart = am4core.create("seeker_path_chart", am4charts.SlicedChart);
    chart.hiddenState.properties.opacity = 0; // this makes initial fade in effect
    chart.data = data.seeker_path

    var series = chart.series.push(new am4charts.PyramidSeries());
    series.dataFields.value = "value";
    series.dataFields.category = "label";
    series.alignLabels = true;
    series.bottomRatio = 1;
    series.topWidth = am4core.percent(100);
    series.bottomWidth = am4core.percent(40);

    series.colors.list = [
      am4core.color("#C7E3FF"),
      am4core.color("#B7D6F3"),
      am4core.color("#A8C9E8"),
      am4core.color("#99BDDD"),
      am4core.color("#8AB0D2"),
      am4core.color("#7BA4C7"),
      am4core.color("#6C97BC"),
      am4core.color("#5D8BB1"),
      am4core.color("#4E7EA6"),
      am4core.color("#3F729B"),
    ].reverse()
  }

  function milestones() {
    let milestones = ``


    data.milestones.forEach( m=>{
      milestones += `<div class="group-progress-button-wrapper" style="flex-basis: 33%">
        <button style="color: white" class="group-progress-button"> ${m.value} </button>
        <p>${m.milestones}</p>
      </div>`
    })
    $("#milestones").html(milestones)

  }

})
