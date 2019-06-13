jQuery(document).ready(function($) {
  let data = wpApiDashboard.data
  console.log(wpApiDashboard);

  update_needed();

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
    label.text =  data.update_needed;
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
      "value": data.update_needed,
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


})
