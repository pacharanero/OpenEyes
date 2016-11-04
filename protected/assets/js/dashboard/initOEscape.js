/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

var VFImages, OCTImages;
var MedChart, IOPchart, VAchart;
var lastIndex = 0;
var currentMedY = 30;
var currentIndexDate = new Date().getTime();
var fixedPoint = {point: undefined, side: undefined, color: undefined};
var defaultTickInterval = 5;
var addHdnSeries = false;

$(document).ready(function() {
    // Create the IOP chart
    IOPchart = new Highcharts.StockChart({
        chart:{
            renderTo: 'iopchart',
            events: {
                click: function (e) {
                    //alert(Highcharts.dateFormat('%A, %b %e, %Y', e.xAxis[0].value));
                    loadAllImages(Highcharts.dateFormat('%Y-%m-%d', e.xAxis[0].value));
                }
            },
            marginLeft: 50,
            spacingLeft: 30,
            marginRight: 50,
            spacingRight: 30
        },
        tooltip: {
            shared:true,
            followTouchMove: false,
            xDateFormat: '<b>%d/%m/%Y</b>',
            
            positioner: function (labelWidth, labelHeight, point) {
              
                var tooltipX, tooltipY;
                if (point.plotX + labelWidth > this.chart.plotWidth) {
                    tooltipX = point.plotX + this.chart.plotLeft - labelWidth - 20;
                } else {
                    tooltipX = point.plotX + this.chart.plotLeft + 20;
                }
              
                tooltipY = this.chart.plotHeight - labelHeight - this.chart.plotTop;
              
                return {
                    x: tooltipX,
                    y: tooltipY
                };
            }
            
        },
        plotOptions: {
            series: {
                states: {
                    hover: {
                        enabled: true,
                        lineWidthPlus: 0,
                    }
                },
                marker:{
                    enabled: true,
                    radius: 4
                }         
            },
            line: {
                events: {
                    legendItemClick: function ( event ) {
                        var eyeSide = event.target.userOptions.side;
                     
                        if(typeof VAchart !== 'undefined'){
                            for(var i = 0; i < VAchart.series.length; i++){
                               
                                if((typeof VAchart.series[i].userOptions.side !== "undefined") 
                                 && (VAchart.series[i].userOptions.side == eyeSide))
                                {
                                    if(VAchart.series[i].visible){
                                        VAchart.series[i].setVisible(false);
                                    } else {
                                        VAchart.series[i].setVisible(true);
                                    }  
                                }
                                
                            }  
                        } 
                        
                        if(typeof MedChart !== 'undefined'){
                            for(var i = 0; i < MedChart.series.length; i++){
                                if((typeof MedChart.series[i].userOptions.side !== "undefined") 
                                 && (MedChart.series[i].userOptions.side == eyeSide))
                                {
                                    if(MedChart.series[i].visible){
                                        MedChart.series[i].setVisible(false);
                                    } else {
                                        MedChart.series[i].setVisible(true);
                                    }  
                                } 
                            } 
                        }
                    }
                },
                showInLegend: true
            }
        },
        rangeSelector : {
            enabled: false,
            inputEnabled: false,
            selected: 5
        },
        legend: {
            enabled: true,
            //floating: true,
            align: 'right',
            verticalAlign: 'top',
            borderColor: '#dddddd',
            borderWidth: 1,
            layout: 'vertical',
            shadow: true,
            y: 24,
            useHTML: true,
        },
        title : {
            text : 'IOP'
        },
        xAxis:{
            labels:{
                enabled: true
            },
            crosshair: {
                snap: false,
                enabled: true
            },
            type: 'datetime',
        },
        yAxis: {
            min: 0,
            max: 60,
            opposite:false,
            //isDirty: true,
            labels:{
                align: 'left',
                x: -20,
                y: -2
            }
        },
        credits: {
            enabled: false
        },
        navigator: {
            enabled: false
        },
        scrollbar : {
            enabled : false
        }
    },function(chart) {
        syncronizeCrossHairs(chart , 'IOPchart');
    });

    $.ajax({
        url: '/OphCiExamination/OEScapeData/GetOperations/'+patientId,
        type: "GET",
        dataType: "json",
        success: function(data) {
            data.forEach(AddOperation, IOPchart);
        },
        cache: false
    });

    
    $.ajax({
        url: '/OphCiExamination/OEScapeData/GetMedications/'+patientId,
        type: "GET",
        dataType: "json",
        success: function(data) {
           
           //data.forEach(AddMedication, MedChart);
            var seriesObj = [];
            var lineHeight = 0.9;
            
            for(var i = 0; i< data.length; i++){

                if(data[i][2] == 1){
                    color = '#ccff99';
                }else{
                    color = '#ff3333';
                }
                
                seriesObj.push({ 
                    name: data[i][3],
                    color: color,
                    side: data[i][2],
                    data: [{ 
                        x : lineHeight,
                        y: data[i][1],
                        low:data[i][0],
                        high:data[i][1],
                        data: [ data[i][0], data[i][1] ],
                    }],
                    dataLabels:{
                        color:'#000',
                        formatter: function(){
                            return this.series.name;
                        },
                        style: {
                            "color": "contrast", 
                            "fontSize": "11px", 
                            "fontWeight": "normal", 
                            "textShadow": "none" 
                        }
                    }  
                });
                
                var lineHeight = lineHeight + 0.9;
            }
            
            /*
            var yMinVals = data.map(function(obj) {
                return obj[0];
            });
            var minValueOfY = Math.min.apply(null, yMinVals);
            */
           
            var maxValueOfMedChart = Math.round(lineHeight);
            var heightOfMedChart = (maxValueOfMedChart + 0.9 ) * currentMedY ;
            
            // create the Medication chart
            MedChart = new Highcharts.chart({
                chart: {
                    renderTo: 'medchart',
                    type: 'columnrange',
                    inverted: true,
                    height: heightOfMedChart,
                    marginLeft: 50,
                    spacingLeft: 30,
                    marginRight: 50,
                    spacingRight: 30
                },   
                title: {
                    text: 'Medications'
                },
                xAxis: {
                    min: 0,
                    max: maxValueOfMedChart,
                    tickInterval: 1,
                    tickColor: '#fff',
                    labels: {
                        enabled: false
                    }
                },
                yAxis: {
                   // min: minValueOfY,
                    startOnTick:false,
                    endOnTick: false,
                    type: 'datetime',
                    labels: {
                        enabled: false
                    },
                    title:{
                        text: '',
                    }, 
                },
                tooltip: {
                    shared:true,
                    xDateFormat: '<b>%d/%m/%Y</b>',
                    useHtml:true,
                    crosshairs: [false, true],
                    formatter: function() {
                        var div = '';
                        var ret = '';

                        $.each(this.points, function(i , e){
                            var startDate = Highcharts.dateFormat('%d/%m/%Y',new Date(e.point.options.low));
                            var endDate = Highcharts.dateFormat('%d/%m/%Y',new Date(e.point.options.high));
                            var name = e.series.name;
                            div = '<div>'+
                                name+'<br />'+
                                startDate+' - '+endDate+'<br /><br />'+
                           '</div>';

                           ret += div;
                        });
                        return ret;
                    }
                },
                plotOptions: {
                    columnrange: {
                        dataLabels: {
                            enabled: true,
                            inside: true,
                            align:'center',
                        }, 
                    }, 
                    series: {
                        pointRange: 1,
                        pointWidth: 15,
                        pointInterval: 1,
                        pointPadding: 0,
                        groupPadding: 0,
                        borderWidth: 0,
                        shadow: false
                    }
                },
                legend: {
                    enabled: false
                },
                series: seriesObj,
                credits: {
                    enabled: false
                }

            },function(chart) {
                syncronizeCrossHairs(chart , 'MedChart');
            });
            
            addSeries(IOPchart, 2, "IOP", "DataSet", "#c653c6", 'solid', 0);
            addSeries(IOPchart, 1, "IOP", "DataSet", "#4d9900", 'solid', 0);
            
        },
        cache: false,
    });
    


    loadAllImages(Highcharts.dateFormat('%Y-%m-%d', new Date().getTime()));
    loadAllVFImages('vfgreyscale');
    loadAllOCTImages('oct');

    $('#vfgreyscale_left, #vfgreyscale_right').mousemove(function(e){
        changeVFImages(e.pageX - this.offsetLeft, $(this).width());
    });

    $('#oct_images').mousemove(function(e){
        changeOCTImages(e.pageX - this.offsetLeft, $(this).width());
    });

    $('.colourplot_left, .colourplot_right').mouseover(function(e){
        if(fixedPoint.point == undefined) {
            var plotId = $(this).attr('id').split('_');
            showRegressionChart(getSideId(plotId[1]), parseInt(plotId[2]), currentIndexDate);
        }
    });

    $('.colourplot_left, .colourplot_right').click(function(e){
        var plotId = $(this).attr('id').split('_');
        //console.log(fixedPointLeft+' '+parseInt(plotId[2]));
        if(fixedPoint.point != undefined && fixedPoint.point == parseInt(plotId[2])){
            $(this).attr('fill', fixedPoint.colour);
            $(this).removeAttr('stroke');
            $(this).removeAttr('stroke-width');
            fixedPoint.point = undefined;
            fixedPoint.side = undefined;
            fixedPoint.colour = undefined;
        }else if(fixedPoint.point == undefined){
            fixedPoint.point = parseInt(plotId[2]);
            fixedPoint.side = getSideId(plotId[1]);
            fixedPoint.colour = $(this).attr('fill');
            $(this).attr('fill','white');
            $(this).attr('stroke','black');
            $(this).attr('stroke-width','4');
        }
    });

    $('.colourplot_left, .colourplot_right').mouseenter(function(e){
        //$(this).addClass('colorplot-hover');
        if(fixedPoint.point == undefined){
            $(this).attr('stroke','black');
            $(this).attr('stroke-width','2');
        }
    });

    $('.colourplot_left, .colourplot_right').mouseout(function(e){
        if(fixedPoint.point == undefined) {
            $(this).removeAttr('stroke');
            $(this).removeAttr('stroke-width');
        }
    });

    $('#vfcolorplot_right, #vfcolorplot_left').mouseout(function(e){
        //$('.regression_chart').hide();
    });

    addRegressionChart();

    // create the Visual Acuity chartxc
    VAchart = new Highcharts.StockChart({
        chart:{
            renderTo: 'vachart',
            marginLeft: 50,
            spacingLeft: 30,
            marginRight: 50,
            spacingRight: 30
        },
        tooltip: {
            shared:true,
            xDateFormat: '<b>%d/%m/%Y</b>',
            positioner: function (labelWidth, labelHeight, point) {
              
                var tooltipX, tooltipY;
                if (point.plotX + labelWidth > this.chart.plotWidth) {
                    tooltipX = point.plotX + this.chart.plotLeft - labelWidth - 20;
                } else {
                    tooltipX = point.plotX + this.chart.plotLeft + 20;
                }
              
                tooltipY = this.chart.plotHeight - labelHeight - this.chart.plotTop;
              
                return {
                    x: tooltipX,
                    y: tooltipY
                };
            }
        },

        rangeSelector : {
            enabled: false,
            inputEnabled: false,
            selected: 5
        },

        plotOptions: {
            series: {
                states: {
                    hover: {
                        enabled: true,
                        lineWidthPlus: 0,
                    }
                },
                marker:{
                    enabled: true,
                    radius: 4
                },
            },
            line: {
                events: {
                    legendItemClick: function ( event ) {
                        
                        var thisChart = this.chart;
                        var eyeSide = event.target.userOptions.side;
                        
                        /*
                         * legend item on/off in the VA chart
                         */
                        var sideArr = [];
                        for(var i = 0; i < thisChart.series.length; i++){
                            if(thisChart.series[i].userOptions.side == eyeSide){
                                
                                if(event.target.index != thisChart.series[i].index){
                                    sideArr.push({
                                        index: thisChart.series[i].index,
                                        visible: thisChart.series[i].visible
                                     });
                                }
                            }
                        }
                        
                        /*
                         * 
                         * @type Array 
                         * The navigator has own series in hidden, so the sideArr array never will empty
                         */
                        $.grep(sideArr, function(e){ 
                            if(e.visible){
                                thisChart.series[e.index].setVisible(false);
                            } else {
                                thisChart.series[e.index].setVisible(true);
                            }
                        });
                      
                        /*
                         * legend item on/off in the IOP chart
                         */
                        if(typeof IOPchart !== 'undefined'){
                            for(var i = 0; i < IOPchart.series.length; i++){
                                if((typeof IOPchart.series[i].userOptions.side !== "undefined") 
                                 && (IOPchart.series[i].userOptions.side == eyeSide))
                                {
                                    if(IOPchart.series[i].visible){
                                        IOPchart.series[i].setVisible(false);
                                    } else {
                                        IOPchart.series[i].setVisible(true);
                                    }  
                                }  
                            } 
                        } 
                        
                        /*
                         * legend item on/off in the Medications chart
                         */
                        
                        if(typeof MedChart !== 'undefined'){
                            for(var i = 0; i < MedChart.series.length; i++){
                                
                                if (MedChart.series[i].userOptions.side == eyeSide){
                                    if(MedChart.series[i].visible){
                                        MedChart.series[i].setVisible(false);
                                    } else {
                                        MedChart.series[i].setVisible(true);
                                    } 
                                }
                            } 
                        } 
                    }
                },
                showInLegend: true
            }
        },

        legend: {
            enabled: true,
            floating: false,
            useHTML: true,
            align: 'right',
            verticalAlign: 'top',
            borderColor: '#dddddd',
            borderWidth: 1,
            layout: 'vertical',
            shadow: true,
            margin: 40,
            y: 5,
            x: -25
        },

        title : {
            text : 'VA/MD'
        },
        xAxis:{
            labels:
            {
                enabled: false
            },
            type: 'datetime',
            crosshair: {
                snap: false
            }
        },
        yAxis: [{
            reversed: true,
            min: -1,
            max: 1,
            opposite:false,
            labels:
            {
                align: 'left',
                x: -20,
                y: -2
            },
            title:{
                text: 'Visual Acuity',
                x: -15
            },
        },{
            min: -15,
            max: 15,
            opposite:false,
            labels:
            {
                enabled:false,
                align: 'right',
                x: 20,
                y: -2
            },
            title:{
                text: 'Mean Deviation',
                x: 22
            },
            opposite: true
        }

        ],
        credits: {
            enabled: false
        },
        navigator: {
            margin: 2,
            height: 20,
            series:{
                lineWidth: 0,
            }
        }
    },function(chart) {
        syncronizeCrossHairs(chart, 'VAchart');
    });


    addSeries(VAchart, 2, "VA", "DataSetVA", "#c653c6", 'solid', 0);
    addSeries(VAchart, 1, "VA", "DataSetVA", "#4d9900", 'solid', 0);

    addSeries(VAchart, 2, 'MD', 'DataSetMD', "#c653c6", 'shortdot', 1);
    addSeries(VAchart, 1, 'MD', 'DataSetMD', "#4d9900", 'shortdot', 1);
    
    //$('#regression_chart').hide();
});

$(document).ajaxStop(function() {
   
    Highcharts.addEvent(VAchart.xAxis[0], 'afterSetExtremes', function (e) {
       
        if (!this.chart.options.chart.isZoomed)
        {
            var xMin = this.chart.xAxis[0].min;
            var xMax = this.chart.xAxis[0].max;
            
            var zmRange = computeTickInterval(xMin, xMax);
            MedChart.yAxis[0].options.tickInterval = zmRange;
            MedChart.yAxis[0].isDirty = true;
            IOPchart.xAxis[0].options.tickInterval = zmRange;
            IOPchart.xAxis[0].isDirty = true;
            VAchart.xAxis[0].options.tickInterval = zmRange;
            VAchart.xAxis[0].isDirty = true;

            MedChart.options.chart.isZoomed = true;
            IOPchart.options.chart.isZoomed = true;
            MedChart.yAxis[0].setExtremes(xMin, xMax, true);

            IOPchart.xAxis[0].setExtremes(xMin, xMax, true);
            IOPchart.options.chart.isZoomed = false;
            MedChart.options.chart.isZoomed = false;
        }
    });
    
   
    if(addHdnSeries == false){
        addHdnSeries = addHiddenSeries();
    }

});

function addHiddenSeries(){
     
    var minXArr = [IOPchart.xAxis[0].dataMin , VAchart.xAxis[0].dataMin, MedChart.yAxis[0].dataMin];
    var maxXArr = [IOPchart.xAxis[0].dataMax , VAchart.xAxis[0].dataMax, MedChart.yAxis[0].dataMax];

    var xMinVals = minXArr.map(function(obj) {
        return obj;
    });
    var xMaxVals = maxXArr.map(function(obj) {
        return obj;
    });
    
    var minX = Math.min.apply(null, xMinVals);
    var maxX = Math.min.apply(null, xMaxVals);
    
   // var minX = IOPchart.xAxis[0].min;
    //var maxX = IOPchart.xAxis[0].max;
    var chartsBreakpoint = incrementTimestamp( minX , maxX );

    IOPchart.addSeries({
        data: chartsBreakpoint,
        enableMouseTracking: false,
        lineWidth: 0,
        showInLegend: false,  
        marker: {
            enabled: false
        },
    });
    
    VAchart.addSeries({
        data: chartsBreakpoint,
        enableMouseTracking: false,
        lineWidth: 0,
        showInLegend: false,  
        marker: {
            enabled: false
        },
    });
   
    
    var maxXlineHeight = MedChart.series.length * 0.9 + 0.9;
   
    MedChart.addSeries({
        name: 'breakpoint',
        side: '3',
        enableMouseTracking: false,
        pointRange: 0,
        pointWidth: 0,
        pointInterval: 0,
        data: [{ 
            x : maxXlineHeight,
            y: maxX,
            low:minX,
            high:maxX,
            data: [ minX, maxX ],
        }],
        dataLabels:{
            enabled:false,
        }  
       
    });
    
    MedChart.yAxis[0].update({
        min: minX,
        max: maxX
    }); 
    
    return true;
}

function incrementTimestamp( startDate , endDate ){
    var result = [];
    var oneDay = 24 * 60 * 60 * 1000;
    var incrementDay = startDate + oneDay;
    
    result.push([ startDate, 0]);
    while (incrementDay <= endDate) {
        result.push([ incrementDay, 0]);
        incrementDay = incrementDay + oneDay;
    }
    result.push([ endDate, 0]);
    
    return result;
}

function redrawCharts(){
    for(var i=0; i<Highcharts.charts.length; i++){
        Highcharts.charts[i].reflow();
    } 
}

function addRegressionChart(){

    $(function () {
        $('#regression_chart').highcharts({
            chart: {
                plotBorderWidth: 1
            },
            xAxis: {
                min: 1,
                gridLineColor: '#333333',
                lineColor: '#333333',
                title: {
                    text: 'Time (months)'
                }
            },
            yAxis: {
                min: 0,
                max: 35,
                gridLineColor: '#DDDDDD',
                lineColor: '#DDDDDD',
                title: {
                    text: 'Sensitivity (dB)'
                }
            },
            title: {
                text: '',
                align: 'left'
            },
            legend:{
                enabled: false
            },
            credits: {
                enabled: false
            },
            series: [{
                type: 'line',
                name: 'Regression Line',
                data: [],
                color: 'black',
                marker: {
                    enabled: false
                },
                states: {
                    hover: {
                        lineWidth: 0
                    }
                },
                enableMouseTracking: false
            }, {
                type: 'scatter',
                name: 'Observations',
                data: [],
                color: '#6699ff',
                marker: {
                    radius: 4,
                    symbol: 'circle'
                }
            }]
        });
    });
}

function updateRegressionChart( data){
    var index=$("#regression_chart").data('highchartsChart');
    Highcharts.charts[index].series[0].setData(data.line, false, false);
    Highcharts.charts[index].series[1].setData(data.plots, false, false);
    $('.highcharts-regressionLabel').remove();
    //regressionLabel = Highcharts.charts[3].renderer.label('Y='+parseFloat(data.regression.m).toFixed(2)+'*x+'+parseFloat(data.regression.b).toFixed(2)+' <b>P=</b> '+parseFloat(data.regression.pb).toFixed(5)+' N='+data.plots.length, 40,30, 'rect', 1, 1, 1, 1, 'regressionLabel').add();
    Highcharts.charts[index].setTitle({text:'Y='+parseFloat(data.regression.m).toFixed(2)+'*x+'+parseFloat(data.regression.b).toFixed(2)+' <b>P=</b> '+parseFloat(data.regression.pb).toFixed(3)+' N='+data.plots.length, align:'left', x:60, style:{"fontSize": "13px"}}, false);
    Highcharts.charts[index].redraw();
}

function showRegressionChart(side, plotNr, indexDate){
    var data = {plots: Array(), line: Array(), regression: Object()};

    data.plots = getPlotData(plotNr, side, indexDate);

    myRegression = linearRegression(data.plots);

    data.line = [[1, myRegression.m*1+myRegression.b],[data.plots.length, myRegression.m*data.plots.length+myRegression.b]];

    data.regression = myRegression;

    updateRegressionChart(data);

    $('#regression_chart').show();
}

function addSeries(chart, side, title, dataurl, seriescol, dashstyle, yaxis){
    $.ajax({
        url: '/OphCiExamination/OEScapeData/'+dataurl+'/'+patientId,
        type: "GET",
        dataType: "json",
        data : {side : side},
        success: function(data) {
            var legindex = 0;
            if(side==1){
                legindex = 1;
            }
            chart.addSeries({
                name: title+" "+getSideName(side),
                data: data,
                color: seriescol,
                legendIndex: legindex,
                zIndex: side,
                dashStyle: dashstyle,
                yAxis: yaxis,
                side: side,
            });
        },
        cache: false
    });
}

function loadAllImages(eventDate){
    loadImage(eventDate, 1, 'vfgreyscale');
    loadImage(eventDate, 2, 'vfgreyscale');
    loadImage(eventDate, 1, 'kowa');
    loadImage(eventDate, 2, 'kowa');
}

function loadImage(eventDate, side, mediaType){
    //console.log('Loading image for patient: '+patientId+' date: '+eventDate);
    $.ajax({
        url: '/OphCiExamination/OEScapeData/LoadImage/'+patientId,
        type: "GET",
        dataType: "html",
        data : {eventDate : eventDate,
                side: side,
                eventType: 'OphInVisualfields',
                mediaType: mediaType},
        success: function(data) {
            //console.log("Image loaded "+data);
            //console.log(mediaType+'_'+getSideName(side));
            $('#'+mediaType+'_'+getSideName(side)).html(data);
        },
        cache: false
    });
}

function getSideName(side){
    if(side==1){
        return 'left';
    }else{
        return 'right';
    }
}

function getSideId(sidename){
    if(sidename=='left'){
        return 1;
    }else{
        return 2;
    }
}


function AddOperation(item, index){
    //console.log(item);

    var color, yshift=10;

    if(item[2] == 1){
        color = '#4d9900';
    }else{
        color = '#c653c6';
    }
    this.xAxis[0].addPlotLine({
        value: item[0],
        color: color,
        width: 2,
        id: 'plot-line-'+index,
        dashStyle: 'Dash',
        label: {
            text: item[1],
            align: 'left',
            y: yshift
        }
    });
}

function AddMedication(item, index){
    
    
    var toValue, color;
    if(item[1] == 0 || item[1] > this.xAxis[0].max){
        toValue = this.xAxis[0].max;
    }else{
        toValue = item[1];
    }
    
    toValue = item[1];
    
    if(item[2] == 1){
        color = '#ccff99';
    }else{
        color = '#ff3333';
    }
    
    this.addSeries({
        name: item[3],
        data: [[item[0],currentMedY,currentMedY-2],[toValue,currentMedY,currentMedY-2]],
        color: color,
        id: 'medication-'+index,
        showInLegend: false,
        enableMouseTracking: true,
        dataLabels:{
            enabled: true,
            inside:true,
            align: 'left',
            formatter: function(){
               return item[3];
            },            
            style: {
                "color": "contrast", 
                "fontSize": "11px", 
                "fontWeight": "normal", 
                //"textShadow": "0 0 6px contrast, 0 0 3px contrast",
                "padding": "0"
            },
        }
    });
   
    currentMedY = currentMedY-2;
    
}

function getPlotData(plotNr, side, dateIndex){
    var i = 0;
    var returnArray = [];
    $.each( VFImages, function(index, data){
        if(parseInt(index) <= parseInt(dateIndex)){
            plotArray = $.parseJSON(data[side][1]);
            //returnArray[i] = [parseInt(index)/1000000, plotArray[plotNr]];
            //returnArray[i] = [Math.round(parseInt(index)/10000000), plotArray[plotNr]];
            returnArray[i] = [i+1, plotArray[plotNr]];
            i++;
        }
    });
/*
    if(side==2 && plotNr==14){
        console.log(returnArray);
    }
*/
    return returnArray
}

function getPlotColour( m, P ){
    //m = m/10;

    //console.log("m: "+m+" P: "+P);
    if(m < -1){
        m = -1;
    }else if(m >= 1){
        m = 0.99;
    }

    if(P < 0.1){
        P = 0.1;
    }
    // colourMatrix: [m value range][p value range][colour code]
    var colourMatrix = [[[-1,-0.9],[0.8,1],"#F4B6B8"],[[-0.9,-0.8],[0.8,1],"#F4B7B1"],[[-0.8,-0.7],[0.8,1],"#F4B6A8"],[[-0.7,-0.6],[0.8,1],"#F4B897"],[[-0.6,-0.5],[0.8,1],"#F5BC8E"],[[-0.5,-0.4],[0.8,1],"#F5BB6F"],[[-0.4,-0.3],[0.8,1],"#F5BB48"],[[-0.3,-0.2],[0.8,1],"#F5C148"],[[-0.2,-0.1],[0.8,1],"#E9C847"],[[-0.1,0],[0.8,1],"#DECB45"],[[0,0.1],[0.8,1],"#D5CF45"],[[0.1,0.2],[0.8,1],"#C5D343"],[[0.2,0.3],[0.8,1],"#B5D643"],[[0.3,0.4],[0.8,1],"#9BDA5C"],[[0.4,0.5],[0.8,1],"#91DA87"],[[0.5,0.6],[0.8,1],"#7BDC9A"],[[0.6,0.7],[0.8,1],"#86DAB1"],[[0.7,0.8],[0.8,1],"#81D9B7"],[[0.8,1],[0.8,1],"#8FD6B9"],[[-1,-0.9],[0.6,0.8],"#EF9699"],[[-0.9,-0.8],[0.6,0.8],"#F09691"],[[-0.8,-0.7],[0.6,0.8],"#EF8D79"],[[-0.7,-0.6],[0.6,0.8],"#EF9069"],[[-0.6,-0.5],[0.6,0.8],"#F0955C"],[[-0.5,-0.4],[0.6,0.8],"#EF923F"],[[-0.4,-0.3],[0.6,0.8],"#F19B41"],[[-0.3,-0.2],[0.6,0.8],"#E7A13F"],[[-0.2,-0.1],[0.6,0.8],"#D9A73E"],[[-0.1,0],[0.6,0.8],"#C1AF3A"],[[0,0.1],[0.6,0.8],"#B8B43A"],[[0.1,0.2],[0.6,0.8],"#AAB738"],[[0.2,0.3],[0.6,0.8],"#98BD37"],[[0.3,0.4],[0.6,0.8],"#80BF45"],[[0.4,0.5],[0.6,0.8],"#66C25E"],[[0.5,0.6],[0.6,0.8],"#60C579"],[[0.6,0.7],[0.6,0.8],"#5DC193"],[[0.7,0.8],[0.6,0.8],"#5DC39A"],[[0.8,1],[0.6,0.8],"#5DC39A"],[[-1,-0.9],[0.4,0.6],"#EC6A75"],[[-0.9,-0.8],[0.4,0.6],"#EC6A69"],[[-0.8,-0.7],[0.4,0.6],"#EC6C58"],[[-0.7,-0.6],[0.4,0.6],"#EB683A"],[[-0.6,-0.5],[0.4,0.6],"#ED703B"],[[-0.5,-0.4],[0.4,0.6],"#DA7B38"],[[-0.4,-0.3],[0.4,0.6],"#D08337"],[[-0.3,-0.2],[0.4,0.6],"#C58834"],[[-0.2,-0.1],[0.4,0.6],"#BA8D34"],[[-0.1,0],[0.4,0.6],"#AE9132"],[[0,0.1],[0.4,0.6],"#A59731"],[[0.1,0.2],[0.4,0.6],"#9D9930"],[[0.2,0.3],[0.4,0.6],"#8F9D2E"],[[0.3,0.4],[0.4,0.6],"#7FA12D"],[[0.4,0.5],[0.4,0.6],"#5AA72A"],[[0.5,0.6],[0.4,0.6],"#53AA36"],[[0.6,0.7],[0.4,0.6],"#52AA64"],[[0.7,0.8],[0.4,0.6],"#51AA78"],[[0.8,1],[0.4,0.6],"#50A982"],[[-1,-0.9],[0.2,0.4],"#DA3F5C"],[[-0.9,-0.8],[0.2,0.4],"#DB414A"],[[-0.8,-0.7],[0.2,0.4],"#DA4334"],[[-0.7,-0.6],[0.2,0.4],"#D84832"],[[-0.6,-0.5],[0.2,0.4],"#C55A2F"],[[-0.5,-0.4],[0.2,0.4],"#BA632E"],[[-0.4,-0.3],[0.2,0.4],"#AE6A2C"],[[-0.3,-0.2],[0.2,0.4],"#A36F2A"],[[-0.2,-0.1],[0.2,0.4],"#9A7529"],[[-0.1,0],[0.2,0.4],"#937728"],[[0,0.1],[0.2,0.4],"#8A7B28"],[[0.1,0.2],[0.2,0.4],"#817E26"],[[0.2,0.3],[0.2,0.4],"#688624"],[[0.3,0.4],[0.2,0.4],"#498920"],[[0.4,0.5],[0.2,0.4],"#459022"],[[0.5,0.6],[0.2,0.4],"#438D52"],[[0.6,0.7],[0.2,0.4],"#418B63"],[[0.7,0.8],[0.2,0.4],"#418B6A"],[[0.8,1],[0.2,0.4],"#418B6A"],[[-1,-0.9],[0.1,0.2],"#BE3348"],[[-0.9,-0.8],[0.1,0.2],"#BD3134"],[[-0.8,-0.7],[0.1,0.2],"#BB2F29"],[[-0.7,-0.6],[0.1,0.2],"#B23227"],[[-0.6,-0.5],[0.1,0.2],"#9E4624"],[[-0.5,-0.4],[0.1,0.2],"#914E21"],[[-0.4,-0.3],[0.1,0.2],"#8B5220"],[[-0.3,-0.2],[0.1,0.2],"#7F581F"],[[-0.2,-0.1],[0.1,0.2],"#795B1F"],[[-0.1,0],[0.1,0.2],"#735E1D"],[[0,0.1],[0.1,0.2],"#6D611C"],[[0.1,0.2],[0.1,0.2],"#66631C"],[[0.2,0.3],[0.1,0.2],"#5D651A"],[[0.3,0.4],[0.1,0.2],"#52681F"],[[0.4,0.5],[0.1,0.2],"#386D17"],[[0.5,0.6],[0.1,0.2],"#346F28"],[[0.6,0.7],[0.1,0.2],"#347043"],[[0.7,0.8],[0.1,0.2],"#316D4F"],[[0.8,1],[0.1,0.2],"#326F54"],[[-1,-0.9],[0.05,0.1],"#8B2436"],[[-0.9,-0.8],[0.05,0.1],"#8B212A"],[[-0.8,-0.7],[0.05,0.1],"#96241F"],[[-0.7,-0.6],[0.05,0.1],"#88211A"],[[-0.6,-0.5],[0.05,0.1],"#783217"],[[-0.5,-0.4],[0.05,0.1],"#6D3B17"],[[-0.4,-0.3],[0.05,0.1],"#683E17"],[[-0.3,-0.2],[0.05,0.1],"#634115"],[[-0.2,-0.1],[0.05,0.1],"#59451D"],[[-0.1,0],[0.05,0.1],"#53451C"],[[0,0.1],[0.05,0.1],"#4F461A"],[[0.1,0.2],[0.05,0.1],"#4C491C"],[[0.2,0.3],[0.05,0.1],"#454A1F"],[[0.3,0.4],[0.05,0.1],"#3E4C22"],[[0.4,0.5],[0.05,0.1],"#2C4F19"],[[0.5,0.6],[0.05,0.1],"#245226"],[[0.6,0.7],[0.05,0.1],"#245333"],[[0.7,0.8],[0.05,0.1],"#24543A"],[[0.8,1],[0.05,0.1],"#24533F"],[[-1,-0.9],[0,0.05],"#61152D"],[[-0.9,-0.8],[0,0.05],"#621525"],[[-0.8,-0.7],[0,0.05],"#62151A"],[[-0.7,-0.6],[0,0.05],"#571D17"],[[-0.6,-0.5],[0,0.05],"#4B261D"],[[-0.5,-0.4],[0,0.05],"#4A2815"],[[-0.4,-0.3],[0,0.05],"#46290C"],[[-0.3,-0.2],[0,0.05],"#3C2E20"],[[-0.2,-0.1],[0,0.05],"#3A2F20"],[[-0.1,0],[0,0.05],"#362F1F"],[[0,0.1],[0,0.05],"#363020"],[[0.1,0.2],[0,0.05],"#343120"],[[0.2,0.3],[0,0.05],"#2F3122"],[[0.3,0.4],[0,0.05],"#2D3325"],[[0.4,0.5],[0,0.05],"#203319"],[[0.5,0.6],[0,0.05],"#1A371F"],[[0.6,0.7],[0,0.05],"#153624"],[[0.7,0.8],[0,0.05],"#153727"],[[0.8,1],[0,0.05],"#15392B"]];

    var minM, maxM, minP, maxP;
    for(var c=0;c<colourMatrix.length;c++){
        minM = colourMatrix[c][0][0];
        maxM = colourMatrix[c][0][1];
        minP = colourMatrix[c][1][0];
        maxP = colourMatrix[c][1][1];
        //console.log(minM+" "+maxM+" m: "+m);
        if((minM <= m && m < maxM) && (minP <= P && P < maxP)){
            return  colourMatrix[c][2];
        }
    }
    /*
    var hsv = RGBtoHSV([0+m,255-m,0]);

    // we change the saturation
    hsv[1] = hsv[1]*Math.abs(P*100);
    var rgb = HSVtoRGB(hsv);

    return 'rgb('+(0+m)+','+(255-m)+',0)';
    //return 'rgb('+rgb[0]+','+rgb[1]+','+rgb[2]+')';
    */
}

function setPlotColours(side, dateIndex){

    //console.log(getPlotData(0, 1, dateIndex));
    var myRegression;
    for(i=0;i<54;i++) {
        //console.log(getPlotData(i, side, dateIndex));
        //myRegression = regression('linear', getPlotData(i, side, dateIndex));
        plotData = getPlotData(i, side, dateIndex);
        myRegression = linearRegression(plotData);
        $('#vfcp_'+getSideName(side)+'_'+i).attr('fill',getPlotColour(myRegression.m, myRegression.pb));
        //console.log(myRegression);
    }
}

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function loadAllVFImages(mediaType){
    $.ajax({
        url: '/OphCiExamination/OEScapeData/LoadAllImages/'+patientId,
        type: "GET",
        dataType: "json",
        data : {
            eventType: 'OphInVisualfields',
            mediaType: mediaType},
        success: function(data) {
            VFImages = data;
            $.each( VFImages, function(index, data){
                $('#vfgreyscale_left_cache').append('<img id="vfg_left_'+index+'" class="vfthumbnail" src="/OphCiExamination/OEScapeData/GetImage/'+data[1][0]+'">');
                $('#vfgreyscale_right_cache').append('<img id="vfg_right_'+index+'" class="vfthumbnail" src="/OphCiExamination/OEScapeData/GetImage/'+data[2][0]+'">');
            });
            setPlotColours(1,new Date().getTime());
            setPlotColours(2,new Date().getTime());
            //console.log("All VF images data loaded ");
        },
        cache: false
    });
}

function loadAllOCTImages(mediaType){
    $.ajax({
        url: '/OphCiExamination/OEScapeData/LoadAllImages/'+patientId,
        type: "GET",
        dataType: "json",
        data : {
            eventType: 'OphInVisualfields',
            mediaType: mediaType},
        success: function(data) {
            OCTImages = data;
            var lastIndex, lastImageId;
            $.each( OCTImages, function(index, data){
                $('#oct_images_cache').append('<img id="oct_'+index+'" class="octimage" src="/OphCiExamination/OEScapeData/GetImage/'+data[3][0]+'">');
                lastIndex = index;
                lastImageId = data[3][0];
            });
            $('#oct_images').append('<img id="oct_'+lastIndex+'" class="octimage" src="/OphCiExamination/OEScapeData/GetImage/'+lastImageId+'">');
        },
        cache: false
    });
}

function changeVFImages(xCoord, imageWidth){
    
    var allImagesNr = Object.keys(VFImages).length;
    var currentIndex = Math.round(xCoord/(imageWidth/allImagesNr));

    i = 0;

    $.each( VFImages, function(index, data){
        if( i == currentIndex && currentIndex != lastIndex){
            //console.log($('#vfgreyscale_left').next('img'));
            $('#vfgreyscale_left').html( $('#vfg_left_'+index).clone() );
            $('#vfgreyscale_right').html( $('#vfg_right_'+index).clone() );
            setPlotColours(1,index);
            setPlotColours(2,index);
            lastIndex = currentIndex;
            currentIndexDate = index;
        }
        i++;
    });
    
    //console.log(xCoord+' imgNr: '+allImagesNr+' width: '+imageWidth+' index: '+currentIndex+' last indx:'+lastIndex);
}

function changeOCTImages(xCoord, imageWidth){
    var allImagesNr = Object.keys(OCTImages).length;
    var currentIndex = Math.round(xCoord/(imageWidth/allImagesNr));

    i = 0;

    $.each( OCTImages, function(index, data){
        if( i == currentIndex && currentIndex != lastIndex){
            //console.log($('#vfgreyscale_left').next('img'));
            $('#oct_images').html( $('#oct_'+index).clone() );
            lastIndex = currentIndex;
        }
        i++;
    });
    //console.log(xCoord+' imgNr: '+allImagesNr+' width: '+imageWidth+' index: '+currentIndex+' last indx:'+lastIndex);
}

//catch mousemove event and have all 3 charts' crosshairs move along indicated values on x axis

function syncronizeCrossHairs(chart , type) {

    var container = $(chart.container),
        offset = container.offset(),
        x, y, isInside, report;

    container.mousemove(function(evt) {
          
          
        x = evt.clientX - chart.plotLeft - offset.left;
        y = evt.clientY - chart.plotTop - offset.top;
        
        var xAxis = chart.xAxis[0];

        //remove old plot line and draw new plot line (crosshair) for this chart
        var xAxis1 = MedChart.yAxis[0];
        xAxis1.removePlotLine("myPlotLineId");
        
        //if we are on the medication chart
        
        switch(type){
            case 'MedChart':
                xAxis1.addPlotLine({
                    value: MedChart.yAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey', 
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId",
                });
            
                var xAxis2 = IOPchart.xAxis[0];
                xAxis2.removePlotLine("myPlotLineId");
                xAxis2.addPlotLine({
                    value: MedChart.yAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });

                var xAxis3 = VAchart.xAxis[0]; 
                xAxis3.removePlotLine("myPlotLineId");

                xAxis3.addPlotLine({
                    value: MedChart.yAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });
            break;
            case 'VAchart':
                //var xAxis3 = VAchart.xAxis[0]; 
                xAxis1.addPlotLine({
                    value: VAchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });
             
                //remove old crosshair and draw new crosshair on chart2
                var xAxis2 = IOPchart.xAxis[0];
                xAxis2.removePlotLine("myPlotLineId");
                xAxis2.addPlotLine({
                    value: VAchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });

                var xAxis3 = VAchart.xAxis[0]; 
                xAxis3.removePlotLine("myPlotLineId");

                xAxis3.addPlotLine({
                    value: VAchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });
            break;
            case 'IOPchart':
               // var xAxis3 = VAchart.xAxis[0]; 
                
                xAxis1.addPlotLine({
                    value: IOPchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });
                
                //remove old crosshair and draw new crosshair on chart2
                var xAxis2 = IOPchart.xAxis[0];
                xAxis2.removePlotLine("myPlotLineId");
                xAxis2.addPlotLine({
                    value: IOPchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });

                var xAxis3 = VAchart.xAxis[0]; 
                xAxis3.removePlotLine("myPlotLineId");

                xAxis3.addPlotLine({
                    value: IOPchart.xAxis[0].translate(x, true),
                    width: 1,
                    color: 'grey',
                    zIndex: 50,
                    //dashStyle: 'dash',
                    id: "myPlotLineId"
                });
            break;
        }
       
        //if you have other charts that need to be syncronized - update their crosshair (plot line) in the same way in this function.
    });
  
}

//compute a reasonable tick interval given the zoom range -
//have to compute this since we set the tickIntervals in order
//to get predictable synchronization between 3 charts with
//different data.
function computeTickInterval(xMin, xMax) {
    var zoomRange = xMax - xMin;

    if (zoomRange <= 2)
        currentTickInterval = 0.5;
    if (zoomRange < 20)
        currentTickInterval = 1;
    else if (zoomRange < 100)
        currentTickInterval = 5;
}

//explicitly set the tickInterval for the 3 charts - based on
//selected range
function setTickInterval(event) {
    var xMin = event.xAxis[0].min;
    var xMax = event.xAxis[0].max;
    computeTickInterval(xMin, xMax);

    MedChart.xAxis[0].options.tickInterval = currentTickInterval;
    MedChart.xAxis[0].isDirty = true;
    IOPchart.xAxis[0].options.tickInterval = currentTickInterval;
    IOPchart.xAxis[0].isDirty = true;
    VAchart.xAxis[0].options.tickInterval = currentTickInterval;
    VAchart.xAxis[0].isDirty = true;
}

//reset the extremes and the tickInterval to default values
function unzoom() {
    MedChart.xAxis[0].options.tickInterval = defaultTickInterval;
    MedChart.xAxis[0].isDirty = true;
    IOPchart.xAxis[0].options.tickInterval = defaultTickInterval;
    IOPchart.xAxis[0].isDirty = true;
    VAchart.xAxis[0].options.tickInterval = defaultTickInterval;
    VAchart.xAxis[0].isDirty = true;

    MedChart.xAxis[0].setExtremes(null, null);
    IOPchart.xAxis[0].setExtremes(null, null);
    VAchart.xAxis[0].setExtremes(null, null);
}

function linearRegression(data){

    var point, ybar=0.0, xbar=0.0;

    var n= data.length;

    for ( var i = 0; i < n ; i++ ) {
        point = data[i];
        ybar = ybar + point[1];
        xbar = xbar + point[0];
    }
    ybar = ybar/(n*1.0);
    xbar = xbar/(n*1.0);

    //console.log('xbar: '+xbar+' ybar: '+ybar);
    var bhat = 0.0;
    var ssqx = 0.0;

    for ( var i = 0; i < n; i++ ) {
        point = data[i];
        bhat = bhat + (point[1] - ybar)*(point[0] - xbar);
        ssqx = ssqx + (point[0] - xbar)*(point[0] - xbar);
    }
    if(ssqx != 0){
        bhat = bhat/ssqx;
    }
    var ahat = ybar - bhat*xbar;

    //console.log('bhat: '+bhat+' ssqx: '+ssqx+' ahat: '+ahat);
    //console.log("n: "+ n);
    //console.log("alpha-hat: "+ ahat);
    //console.log("beta-hat: "+ bhat);

    var sigmahat2 = 0.0;
    var ri = new Array(n);
    for ( var i = 0; i < n; i++ ) {
        point = data[i];
        ri[i] = point[1] - (ahat + bhat*point[0]);
        sigmahat2 = sigmahat2 + ri[i]*ri[i];
    }
    sigmahat2 = sigmahat2 / ( n*1.0 - 2.0 );

    //console.log("sigma-hat square: "+ sigmahat2);

    var seb = Math.sqrt(sigmahat2/ssqx);

    //console.log("se(b): "+ seb);

    var sigmahat = Math.sqrt((seb*seb)*ssqx);
    //console.log("sigma-hat: "+ sigmahat);

    var sea = Math.sqrt(sigmahat*sigmahat * ( 1 /(n*1.0) + xbar*xbar/ssqx));

    //console.log("se(a): "+ sea);

    var Tb = (bhat - 0.0) / seb;

    pvalb = studpval(Tb, n);
    //console.log("pval B "+pvalb);

    var Ta = (ahat - 0.0) / sea;
    pvala = studpval(Ta, n);
    //console.log("pval A "+pvala);

    return{
        m: bhat,
        b: ahat,
        pa: pvala,
        pb: pvalb
    }
}

function statcom ( mq, mi, mj, mb )
{
    zz = 1;
    mz = zz;
    mk = mi;
    while ( mk <= mj ) {
        zz = zz * mq * mk / ( mk - mb);
        mz = mz + zz;
        mk = mk + 2;
    }
    return mz;
}

function studpval ( mt , mn )
{
    PI = 3.1415926535897932384626433832795028841971693993751058209749445923078164062862089986280348253421170679;
    if ( mt < 0 )
        mt = -1*mt;
    mw = mt / Math.sqrt(mn);
    th = Math.atan2(mw, 1);
    if ( mn == 1 )
        return 1.0 - th / (PI/2.0);
    sth = Math.sin(th);
    cth = Math.cos(th);
    if ( mn % 2 == 1 )
        return 1.0 - (th+sth*cth*statcom(cth*cth, 2, mn-3, -1))/(PI/2.0);
    else
        return 1.0 - sth * statcom(cth*cth, 1, mn-3, -1);
}

function RGBtoHSV(color) {
    var r,g,b,h,s,v;
    r= color[0];
    g= color[1];
    b= color[2];
    min = Math.min( r, g, b );
    max = Math.max( r, g, b );


    v = max;
    delta = max - min;
    if( max != 0 )
        s = delta / max;        // s
    else {
        // r = g = b = 0        // s = 0, v is undefined
        s = 0;
        h = -1;
        return [h, s, undefined];
    }
    if( r === max )
        h = ( g - b ) / delta;      // between yellow & magenta
    else if( g === max )
        h = 2 + ( b - r ) / delta;  // between cyan & yellow
    else
        h = 4 + ( r - g ) / delta;  // between magenta & cyan
    h *= 60;                // degrees
    if( h < 0 )
        h += 360;
    if ( isNaN(h) )
        h = 0;
    return [h,s,v];
};

function HSVtoRGB(color) {
    var i;
    var h,s,v,r,g,b;
    h= color[0];
    s= color[1];
    v= color[2];
    if(s === 0 ) {
        // achromatic (grey)
        r = g = b = v;
        return [r,g,b];
    }
    h /= 60;            // sector 0 to 5
    i = Math.floor( h );
    f = h - i;          // factorial part of h
    p = v * ( 1 - s );
    q = v * ( 1 - s * f );
    t = v * ( 1 - s * ( 1 - f ) );
    switch( i ) {
        case 0:
            r = v;
            g = t;
            b = p;
            break;
        case 1:
            r = q;
            g = v;
            b = p;
            break;
        case 2:
            r = p;
            g = v;
            b = t;
            break;
        case 3:
            r = p;
            g = q;
            b = v;
            break;
        case 4:
            r = t;
            g = p;
            b = v;
            break;
        default:        // case 5:
            r = v;
            g = p;
            b = q;
            break;
    }
    return [r,g,b];
}
