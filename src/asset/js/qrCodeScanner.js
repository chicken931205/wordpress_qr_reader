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
    if (!param_enable.is_logged_in) {
      qrWarning.hidden = false;
      warningData.innerText = "You must log in.";
      return;
    }

    var redirect_url = result.data;
    var param_set = false;
    if (param_enable.team_id_enable && param_enable.team_id) {
      redirect_url = `${redirect_url}?team_id=${param_enable.team_id}`;
      param_set = true;
    }

    if (param_enable.minecraft_id_enable && param_enable.minecraft_id) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}minecraft_id=${param_enable.minecraft_id}`;

      param_set = true;
    }

    if (param_enable.server_id_enable && param_enable.server_id) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}server_id=${param_enable.server_id}`;
      param_set = true;
    }

    if (param_enable.game_id_enable && param_enable.game_id) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}game_id=${param_enable.game_id}`;
      param_set = true;
    }

    if (param_enable.group_id_enable && param_enable.group_id) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}group_id=${param_enable.group_id}`;
      param_set = true;
    }

    if (param_enable.gamipress_ranks_enable && param_enable.user_rank) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}user_rank=${param_enable.user_rank}`;
      param_set = true;
    }

    if (param_enable.gamipress_points_enable && param_enable.user_points) {
      if (param_set) {
        redirect_url = `${redirect_url}%`;
      } else {
        redirect_url = `${redirect_url}?`;
      }
      redirect_url = `${redirect_url}user_points=${param_enable.user_points}`;
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