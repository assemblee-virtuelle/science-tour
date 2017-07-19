<?php
namespace TheScienceTour\MapBundle\Helper;

use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlays\MarkerCluster;
use Ivory\GoogleMap\Helper\Overlays\MarkerCluster\JsMarkerClusterHelper;

class TSTMarkerClusterHelper extends JsMarkerClusterHelper {
	
    public function renderLibraries(MarkerCluster $markerCluster, Map $map) {
    	
    	$tst_script = '
<script type="text/javascript" src="//google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js"></script>
<script type="text/javascript">
    var myPositionMarkerPath = "img/markers/my-position.png";

    /**
     *
	 * Avoid myPosition-marker clustering with project-marker
     *
	 */
	MarkerClusterer.prototype.addToClosestCluster_ = function(marker) {
    	// if myPosition-marker, a new isolated cluster is created
    	if (marker.getIcon()["url"].indexOf(myPositionMarkerPath) >= 0) {
	    	var cluster = new Cluster(this);
	    	cluster.addMarker(marker);
	    	this.clusters_.push(cluster);
    	} else {
			var distance = 40000;
			var clusterToAddTo = null;
			var pos = marker.getPosition();
			for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
    			// nearest cluster can not be the myPosition-marker cluster
				if (cluster.markers_[0].getIcon()["url"].indexOf(myPositionMarkerPath) == -1) {
			    	var center = cluster.getCenter();
			    	if (center) {
			      		var d = this.distanceBetweenPoints_(center, marker.getPosition());
			      		if (d < distance) {
			        		distance = d;
			        		clusterToAddTo = cluster;
			      		}
			    	}
		    	}
		  	}
		  	if (clusterToAddTo && clusterToAddTo.isMarkerInClusterBounds(marker)) {
		    	clusterToAddTo.addMarker(marker);
		  	} else {
		    	var cluster = new Cluster(this);
		    	cluster.addMarker(marker);
		    	this.clusters_.push(cluster);
		  	}
    	}
	};

    			
	/**
     *
	 * Disable click trigger on myPosition-marker cluster
     * If zoom is max, display infowindow (merging markers infowindows)
     *
	 */    			
	ClusterIcon.prototype.triggerClusterClick = function() {
    	if (this.url_.indexOf(myPositionMarkerPath) == -1) {	
			var markerClusterer = this.cluster_.getMarkerClusterer();
			google.maps.event.trigger(markerClusterer, \'clusterclick\', this.cluster_);
			var mz = this.cluster_.markerClusterer_.getMaxZoom();
    		if (mz && this.map_.getZoom() >= mz) {
    			if (this.cluster_.infowindow) {
    				this.cluster_.infowindow.open(this.map_, this.cluster_.markers_[0]);
    			} else {
    				var contentString = "";
    				for (var i = 0, marker; marker = this.cluster_.markers_[i]; i++) {
    					if (marker.infowindow) {
    						contentString += marker.infowindow.getContent();
    					}
    				}
    				if (contentString != "") {
	    				contentString = "<div class=\"cluster-infoWindow\">" + contentString + "</div>";
	    				this.cluster_.infowindow = new google.maps.InfoWindow({
	    					content: contentString
	    				});
	    				this.cluster_.infowindow.open(this.map_, this.cluster_.markers_[0]);
    				}
    			}
    			
			} else {
				if (markerClusterer.isZoomOnClick()) {
					this.map_.fitBounds(this.cluster_.getBounds());
				}		
    		}
    	}
	};
    			

	/**
     *
	 * Add z-index & pointer management
     *
	 */   
	ClusterIcon.prototype.createCss = function(pos) {
		var style = [];
		style.push(\'background-image:url(\' + this.url_ + \');\');
		var backgroundPosition = this.backgroundPosition_ ? this.backgroundPosition_ : \'0 0\';
		style.push(\'background-position:\' + backgroundPosition + \';\');
		if (typeof this.anchor_ === \'object\') {
			if (typeof this.anchor_[0] === \'number\' && this.anchor_[0] > 0 && this.anchor_[0] < this.height_) {
				style.push(\'height:\' + (this.height_ - this.anchor_[0]) + \'px; padding-top:\' + this.anchor_[0] + \'px;\');
			} else {
				style.push(\'height:\' + this.height_ + \'px; line-height:\' + this.height_ + \'px;\');
			}
			if (typeof this.anchor_[1] === \'number\' && this.anchor_[1] > 0 && this.anchor_[1] < this.width_) {
				style.push(\'width:\' + (this.width_ - this.anchor_[1]) + \'px; padding-left:\' + this.anchor_[1] + \'px;\');
			} else {
				style.push(\'width:\' + this.width_ + \'px; text-align:center;\');
			}
		} else {
			style.push(\'height:\' + this.height_ + \'px; line-height:\' + this.height_ + \'px; width:\' + this.width_ + \'px; text-align:center;\');
		}
		var txtColor = this.textColor_ ? this.textColor_ : \'black\';
		var txtSize = this.textSize_ ? this.textSize_ : 11;
		var zindex = this.zindex_ ? this.zindex_ : "auto";
		var cursor = this.zindex_ ? "auto" : "pointer";
		style.push(\'cursor:\' + cursor + \'; top:\' + pos.y + \'px; left:\' + pos.x + \'px; color:\' + txtColor + \'; position:absolute; font-size:\' + txtSize + \'px; z-index:\' + zindex + \'; font-family:Arial,sans-serif; font-weight:bold\');
		return style.join(\'\');
	};
    			
 
    /**
     *
	 * Set myPosition-marker cluster icon to the myPosition-marker icon
	 * Hide myPosition-marker
     * Set great number to myPosition-marker cluster z-index
     *
	 */    						
	Cluster.prototype.updateIcon = function() {
		if (this.markers_.length < this.minClusterSize_) {		
			var clu_icon_name = this.markers_[0].getIcon()["url"].substr(this.markers_[0].getIcon()["url"].length - 18);
			if (this.markers_[0].getIcon()["url"].indexOf(myPositionMarkerPath) >= 0) {
				var numStyles = this.markerClusterer_.getStyles().length;
				var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
				this.clusterIcon_.setCenter(this.center_);
				this.clusterIcon_.setSums(sums);
				this.clusterIcon_.sums_.text = "";
				this.clusterIcon_.url_ = this.markers_[0].getIcon().url;
				this.clusterIcon_.height_ = 24;
				this.clusterIcon_.width_ = 24;
    			this.clusterIcon_.zindex_ = "999999999";
				this.markers_[0].setVisible(false);
				this.clusterIcon_.show();
			} else {
				this.clusterIcon_.hide();
			}
			return;
		}
		var numStyles = this.markerClusterer_.getStyles().length;
		var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
		this.clusterIcon_.setCenter(this.center_);
		this.clusterIcon_.setSums(sums);
		this.clusterIcon_.show();
	};

</script>
		';
    	
        return $tst_script.PHP_EOL;
    }

}
