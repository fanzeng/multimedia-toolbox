
const BATCH_SIZE = 1;
$(document).ready(function () {
  window.allFileList = Array();
  let fileInput = $('#files-to-upload')[0];
  fileInput.addEventListener('change', () => {
    let fileList = Array();
    let numberAddedFiles = 0;
    for (let i = 0; i < fileInput.files.length; i++) {
      fileList.push(fileInput.files[i]);
      numberAddedFiles++;
      if (numberAddedFiles >= BATCH_SIZE) {
        allFileList.push(fileList);
        fileList = [];
        numberAddedFiles = 0;
      }
    }
    if (numberAddedFiles > 0) {
      allFileList.push(fileList);
    }
  });
});



function onBrowseBtnClick() {
  let fileCatcher = $('#form-upload-files')[0];
  console.log('fileCatcher=', fileCatcher)
  let fileInput = $('#files-to-upload')[0];
  let sendFileList;
  fileCatcher.addEventListener('submit', function (event) {
    event.preventDefault();
    sendFileList(window.allFileList, 0);
  });
  sendFileList = function (allFileList, i) {
    fileList = allFileList[i];
    fileInput.files = new FileListItem(fileList.reverse());
    function FileListItem(a) {
      a = [].slice.call(Array.isArray(a) ? a : arguments);
      for (var c, b = c = a.length, d = !0; b-- && d;) d = a[b] instanceof File;
      if (!d) throw new TypeError('expected argument to FileList is File or array of File objects');
      for (b = (new ClipboardEvent('')).clipboardData || new DataTransfer; c--;) b.items.add(a[c]);
      return b.files;
    }
    let formData = new FormData(fileCatcher);
    let request = new XMLHttpRequest();
    request.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        i++;
        if (i < allFileList.length) {
          sendFileList(allFileList, i);
        } else {
          $('#upload-images-message').html('Finished.');
          window.location.reload(false);
        }
      }
    };
    $('#upload-images-message').html('Uploading batch ' + String(i + 1) + '/' + String(allFileList.length) + ' of ' + String(fileList.length) + ' images, please wait.');
    request.open('POST', 'video_creator.php', false);
    request.send(formData);
  };
}
