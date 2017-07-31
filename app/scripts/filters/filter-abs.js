angular.module('sgdp.filter-abs',[]).filter('abs', function() {
    return function(num) { return Math.abs(num); }
});