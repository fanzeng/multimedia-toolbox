$(document).ready(function () {
});



function onBrowseBtnClick () {
	var fileCatcher = $("#upload_files")[0];
    var fileInput = $("#files_to_upload")[0];
	var allFileList = Array();
	var sendFileList;

 	fileCatcher.addEventListener("submit", function (event) {
	  	event.preventDefault();
   	 	sendFileList(allFileList, 0);
  	});
  
    fileInput.addEventListener("change", function (evnt) {
 		var fileList = Array();
 		var numberAddedFiles = 0;
  		for (var i = 0; i < fileInput.files.length; i++) {
    		fileList.push(fileInput.files[i]);
    		numberAddedFiles++;
			if (numberAddedFiles >= 20) {
				allFileList.push(fileList);
				fileList = [];
				numberAddedFiles = 0;
			}
		}
		if (numberAddedFiles > 0) {
			allFileList.push(fileList);
		}

    });

  
    sendFileList = function (allFileList, i) {

		fileList = allFileList[i];
		fileInput.files = new FileListItem(fileList.reverse());

		function FileListItem(a) {
		  a = [].slice.call(Array.isArray(a) ? a : arguments);
		  for (var c, b = c = a.length, d = !0; b-- && d;) d = a[b] instanceof File;
		  if (!d) throw new TypeError("expected argument to FileList is File or array of File objects");
		  for (b = (new ClipboardEvent("")).clipboardData || new DataTransfer; c--;) b.items.add(a[c]);
		  return b.files;
		}


  		var formData = new FormData(fileCatcher);
    	var request = new XMLHttpRequest();
 		
	    request.onreadystatechange = function () {
    	    if (this.readyState === 4 && this.status === 200) {
				i++;
				if (i < allFileList.length) {
					sendFileList(allFileList, i); 
				} else {
					$("#upload_images_message").html("Finished.");
					window.location.reload(false);
				}
    	    }
    	};
		$("#upload_images_message").html("Uploading batch " + String(i+1) + "/" + String(allFileList.length)  + " of " + String(fileList.length) + " images, please wait.");
    	request.open("POST", "video_creator.php", false);
	   	request.send(formData);
  };
}

