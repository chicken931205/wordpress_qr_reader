import QrScanner from "./qr-scanner.min.js";

const video = document.createElement("video");

const camQrResult = document.getElementById('outputData');
const qrResult = document.getElementById("qr-result");
const qrWarning = document.getElementById("qr-warning");
const warningData = document.getElementById("warningData");
const btnScanQR = document.getElementById("btn-scan-qr");
const btnStopScan = document.getElementById("btn-stop-scan");

function stop_scan() {
  qrResult.hidden = false;
  btnScanQR.hidden = false;
  btnStopScan.hidden = true;

  scanner.stop();
}

function start_scan() {
  qrResult.hidden = true;
  qrWarning.hidden = true;
  btnScanQR.hidden = true;
  btnStopScan.hidden = false;

  scanner.start();
}

function setResult(label, result) {
  stop_scan();

  console.log(result.data);
  label.textContent = result.data;
  
  setTimeout(() => {
    if (!user_profile.is_logged_in) {
      qrWarning.hidden = false;
      warningData.innerText = "You must log in.";
      return;
    }

    if (user_profile.game_id && user_profile.team_id && user_profile.group_id) {
      window.location.href = `${res}?game_id=${user_profile.game_id}&team_id=${user_profile.team_id}&group_id=${user_profile.group_id}`;
    } else {
      qrWarning.hidden = false;
      warningData.innerText = "You must set Gameplay info in the User profile page.";
    }
  }, 1000);

}

const scanner = new QrScanner(video, result => setResult(camQrResult, result), {
  onDecodeError: error => {
      camQrResult.textContent = error;
      camQrResult.style.color = 'inherit';
  },
  highlightScanRegion: true,
  highlightCodeOutline: true,
});

// for debugging
window.scanner = scanner;

btnScanQR.onclick = () => {
  start_scan();
};

btnStopScan.onclick = () => {
 stop_scan();
}