function BeaconClient(proxyPath,connectionType,beaconURL) {

	this.tagMatcher = /<.*?>/g;
	this.beaconURL = beaconURL || "http://beacon.literalart.net";
	this.proxyPath = proxyPath || undefined;
	this._response = "";
	if (this.proxyPath) {
		// console.log("proxy set ..."); // DEBUG
		this.beaconURL = proxyPath + "?url=beacon.literalart.net"
	}
	
	this.connectionType = connectionType || undefined;
	// console.log(this.connectionType); // DEBUG
	
	if (connectionType) {
		switch (connectionType) {
			case "v": // verbose mode
				this.beaconURL += "?p=v";
				break;
			case "j": // TODO placeholder for a JSONP mode
				this.beaconURL += "?p=j";
				break;
			case "k": // TODO lighthouse keeper lhk mode
				this.beaconURL += "?p=k";
				break;
		}
	}
	// console.log(this.beaconURL); // DEBUG
	
	this.cmdStr = "0";
	this.statesStr = "12340120";
	this.request = new XMLHttpRequest();
	
}

BeaconClient.prototype = {

	doStatesStr : function() {
		this.response = this.pollBeacon();
		this.response = this.response.replace(this.tagMatcher, ""); // TODO
		this.cmdStr = this.response.substr(0, 1);
		this.statesStr = this.response.substr(1, 8);
		return this.statesStr;
	},
	
	doFirstSet : function(poll) {
		if (poll) this.statesStr = doStatesStr();
		if (!this.connectionType) {
			return this.statesStr.substr(0, 4).split("");
		}
		return undefined;
	},
	
	doSecondSet : function(poll) {
		if (poll) this.statesStr = doStatesStr();
		if (!this.connectionType) {
			return this.statesStr.substr(4, 4).split("");
		}
		return undefined;
	},

	doCmdStr : function(poll) {
		if (poll) this.statesStr = this.doStatesStr();
		return this.cmdStrsubstr(0, 1);
	},
	
	pollBeacon : function() {
		this.request.open("GET", this.beaconURL, false); // no callback
		this.request.send(null);
		// console.log(this.request.responseText); // DEBUG
		return this.request.responseText;
	}
	
};


