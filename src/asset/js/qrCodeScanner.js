import QrScanner from "./qr-scanner.min.js";

const video = document.getElementById('qr-video');
const videoContainer = document.getElementById('video-container');

const outputData = document.getElementById('outputData');
const qrResult = document.getElementById("qr-result");
const qrWarning = document.getElementById("qr-warning");
const warningData = document.getElementById("warningData");
const btnScanQR = document.getElementById("btn-scan-qr");
const btnStopScan = document.getElementById("btn-stop-scan");

function stop_scan() {
  qrResult.hidden = false;
  btnScanQR.hidden = false;
  btnStopScan.hidden = true;
  videoContainer.hidden = true;

  scanner.stop();
}

function start_scan() {
  qrResult.hidden = true;
  qrWarning.hidden = true;
  btnScanQR.hidden = true;
  btnStopScan.hidden = false;
  videoContainer.hidden = false;

  scanner.start();
}

function setResult(label, result) {
  stop_scan();

  console.log(`embed code: ${result.data}`);
  label.textContent = result.data;
  
  setTimeout(() => {
    if (!user_profile.is_logged_in) {
      qrWarning.hidden = false;
      warningData.innerText = "You must log in.";
      return;
    }

    var redirect_url = result.data;
    var param_set = false;
    if (user_profile.team_id_enable && user_profile.team_id) {
      redirect_url = `${redirect_url}?team_id=${user_profile.team_id}`;
      param_set = true;
    }

    if (user_profile.minecraft_id_enable && user_profile.minecraft_id) {
      redirect_url = `${redirect_url}?minecraft_id=${user_profile.minecraft_id}`;
      param_set = true;
    }

    if (user_profile.server_id_enable && user_profile.server_id) {
      redirect_url = `${redirect_url}?server_id=${user_profile.server_id}`;
      param_set = true;
    }

    if (user_profile.game_id_enable && user_profile.game_id) {
      redirect_url = `${redirect_url}?game_id=${user_profile.game_id}`;
      param_set = true;
    }

    if (user_profile.group_id_enable && user_profile.group_id) {
      redirect_url = `${redirect_url}?group_id=${user_profile.group_id}`;
      param_set = true;
    }

    if (user_profile.gamipress_ranks_enable && user_profile.user_rank) {
      redirect_url = `${redirect_url}?user_rank=${user_profile.user_rank}`;
      param_set = true;
    }

    if (user_profile.gamipress_points_enable && user_profile.user_points) {
      redirect_url = `${redirect_url}?user_points=${user_profile.user_points}`;
      param_set = true;
    }

    if (param_set) {
      window.location.href = redirect_url;
    } else {
      qrWarning.hidden = false;
      warningData.innerText = "You must set parameters in the User profile page and QR Reader Settings page.";
    }
  }, 1000);

}

const scanner = new QrScanner(video, result => setResult(outputData, result), {
  onDecodeError: error => {
      outputData.textContent = error;
      outputData.style.color = 'inherit';
      qrResult.hidden = false;
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