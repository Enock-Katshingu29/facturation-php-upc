/**
 * Scanner de code-barres avec QuaggaJS
 * Étudiant 2 : Front-End & Hardware
 * 
 * Utilisation:
 * - Inclure ce script dans la page
 * - Appeler initScanner() pour démarrer
 * - Le callback onScanSuccess sera appelé avec le code-barre
 */

// Configuration du scanner
let Quagga = null;
let isScanning = false;
let scannerInitialized = false;
let scannerSessionId = 0;
let activeQuaggaSessionId = 0;
let quaggaHandlerAttached = false;
const scannerScriptUrl = document.currentScript ? document.currentScript.src : '';
let scanOptions = {};
let scanCandidates = new Map();

// Callback appelé quand un code-barre est détecté
let onScanSuccess = null;
let onScanError = null;

function getScannerAssetUrl(fileName) {
    if (scannerScriptUrl) {
        return new URL(fileName, scannerScriptUrl).href;
    }

    return '../../assets/js/' + fileName;
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;

        document.head.appendChild(script);
    });
}

/**
 * Charger la bibliothèque QuaggaJS dynamiquement
 */
async function loadQuaggaJS() {
    if (window.Quagga) {
        Quagga = window.Quagga;
        return Quagga;
    }

    const sources = [
        getScannerAssetUrl('quagga.min.js'),
        'https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js'
    ];

    for (const source of sources) {
        try {
            await loadScript(source);
            Quagga = window.Quagga;

            if (Quagga) {
                return Quagga;
            }
        } catch (error) {
            console.warn('Chargement QuaggaJS échoué:', source, error);
        }
    }

    throw new Error('Échec du chargement de QuaggaJS');
}

function supportsBarcodeDetector() {
    return 'BarcodeDetector' in window;
}

function getCameraErrorMessage(error) {
    if (!error) {
        return "Impossible de démarrer le scanner.";
    }

    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
        return "Accès caméra refusé. Autorisez la caméra dans le navigateur, puis rechargez la page.";
    }

    if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
        return "Aucune caméra détectée sur cet appareil.";
    }

    if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
        return "La caméra est déjà utilisée par une autre application ou le navigateur ne peut pas y accéder.";
    }

    if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
        return "La caméra ne supporte pas les réglages demandés. Essayez une autre caméra ou un autre navigateur.";
    }

    if (error.message === 'Échec du chargement de QuaggaJS') {
        return "Le scanner de secours n'a pas pu se charger. Vérifiez que le fichier assets/js/quagga.min.js existe.";
    }

    return error.message || "Impossible de démarrer le scanner.";
}

function getQuaggaReaders() {
    const knownCodes = scanOptions.knownCodes || [];
    const readers = [];

    if (knownCodes.length === 0 || knownCodes.some(code => /^\d{13}$/.test(code))) {
        readers.push("ean_reader");
    }

    if (knownCodes.length === 0 || knownCodes.some(code => /^\d{8}$/.test(code))) {
        readers.push("ean_8_reader");
    }

    if (knownCodes.length === 0 || knownCodes.some(code => /[a-z]/i.test(code))) {
        readers.push("code_128_reader", "code_39_reader");
    }

    return readers.length > 0 ? readers : ["ean_reader", "ean_8_reader"];
}

function getNativeBarcodeFormats() {
    const knownCodes = scanOptions.knownCodes || [];
    const formats = [];

    if (knownCodes.length === 0 || knownCodes.some(code => /^\d{13}$/.test(code))) {
        formats.push("ean_13");
    }

    if (knownCodes.length === 0 || knownCodes.some(code => /^\d{8}$/.test(code))) {
        formats.push("ean_8");
    }

    if (knownCodes.length === 0 || knownCodes.some(code => /[a-z]/i.test(code))) {
        formats.push("code_128", "code_39");
    }

    return formats.length > 0 ? formats : ["ean_13", "ean_8"];
}

/**
 * Initialiser le scanner avec la caméra
 * @param {string} targetElement - ID de l'élément cible pour le scanner
 * @param {function} successCallback - Callback en cas de scan réussi
 * @param {function} errorCallback - Callback en cas d'erreur
 */
async function initScanner(targetElement, successCallback, errorCallback) {
    if (isScanning) {
        console.warn('Scanner déjà en cours');
        return;
    }

    const target = document.querySelector(targetElement);

    if (!target) {
        const error = new Error(`Zone du scanner introuvable: ${targetElement}`);
        if (typeof errorCallback === 'function') {
            errorCallback(error);
        }
        return;
    }

    const sessionId = ++scannerSessionId;
    
    onScanSuccess = successCallback;
    onScanError = errorCallback;
    scanCandidates = new Map();
    lastCode = null;
    lastTime = 0;
    
    try {
        // Charger QuaggaJS
        Quagga = await loadQuaggaJS();

        if (sessionId !== scannerSessionId || !target.isConnected) {
            return;
        }

        if (quaggaHandlerAttached && typeof Quagga.offDetected === 'function') {
            Quagga.offDetected(handleDetected);
            quaggaHandlerAttached = false;
        }

        activeQuaggaSessionId = sessionId;
        
        // Configurer Quagga
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: target,
                constraints: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "environment" // Utiliser la caméra arrière si disponible
                },
                area: {
                    top: "18%",
                    right: "15%",
                    bottom: "18%",
                    left: "15%"
                }
            },
            numOfWorkers: navigator.hardwareConcurrency ? Math.min(navigator.hardwareConcurrency, 2) : 1,
            frequency: 6,
            decoder: {
                readers: getQuaggaReaders()
            },
            locate: true,
            locator: {
                patchSize: "large",
                halfSample: false
            }
        }, function(err) {
            if (sessionId !== scannerSessionId || !target.isConnected) {
                if (activeQuaggaSessionId === sessionId) {
                    try {
                        Quagga.stop();
                    } catch (error) {
                        console.warn('Arrêt de la caméra annulée incomplet:', error);
                    }
                    activeQuaggaSessionId = 0;
                }
                return;
            }

            if (err) {
                console.error('Erreur QuaggaJS:', err);
                activeQuaggaSessionId = 0;
                scannerInitialized = false;
                if (onScanError) onScanError(err);
                return;
            }
            
            try {
                Quagga.start();
                isScanning = true;
                scannerInitialized = true;
                console.log('Scanner démarré');
            } catch (error) {
                activeQuaggaSessionId = 0;
                scannerInitialized = false;
                console.error('Démarrage QuaggaJS impossible:', error);
                if (onScanError) onScanError(error);
            }
        });
        
        // Gestionnaire de détection
        Quagga.onDetected(handleDetected);
        quaggaHandlerAttached = true;
        
    } catch (error) {
        if (sessionId !== scannerSessionId) {
            return;
        }

        if (activeQuaggaSessionId === sessionId) {
            activeQuaggaSessionId = 0;
        }

        console.error('Erreur initialization:', error);
        if (onScanError) onScanError(error);
    }
}

/**
 * Gérer la détection d'un code-barre
 */
let lastCode = null;
let lastTime = 0;

function normalizeScannedCode(code) {
    return String(code || '').trim();
}

function isKnownProductCode(code) {
    if (!scanOptions.knownCodes || scanOptions.knownCodes.length === 0) {
        return true;
    }

    return scanOptions.knownCodes.includes(code);
}

function isValidEanChecksum(code) {
    if (!/^\d{8}$|^\d{13}$/.test(code)) {
        return false;
    }

    const digits = code.split('').map(Number);
    const checkDigit = digits.pop();
    const sum = digits.reduce((total, digit, index) => {
        const isEvenLength = code.length === 8;
        const weight = (isEvenLength ? index % 2 === 0 : index % 2 !== 0) ? 3 : 1;
        return total + digit * weight;
    }, 0);

    return (10 - (sum % 10)) % 10 === checkDigit;
}

function getDetectionError(result) {
    if (!result.codeResult || !Array.isArray(result.codeResult.decodedCodes)) {
        return 1;
    }

    const errors = result.codeResult.decodedCodes
        .map(decodedCode => decodedCode.error)
        .filter(error => typeof error === 'number');

    if (errors.length === 0) {
        return 0;
    }

    return errors.reduce((total, error) => total + error, 0) / errors.length;
}

function updateScanStatus(message, color = '#4d5b68') {
    const status = document.querySelector('#scanner-modal .scanner-status');

    if (status) {
        status.textContent = message;
        status.style.color = color;
    }
}

function countScanCandidate(code, resetAfter = 1500) {
    const candidate = scanCandidates.get(code) || { count: 0, lastSeen: 0 };
    const now = Date.now();
    const count = now - candidate.lastSeen > resetAfter ? 1 : candidate.count + 1;

    scanCandidates.set(code, { count, lastSeen: now });
    return count;
}

function shouldAcceptScannedCode(code, result) {
    if (!isKnownProductCode(code)) {
        const count = countScanCandidate(code);

        if (count >= 3) {
            updateScanStatus(
                `Code ${code} détecté, mais ce produit n'est pas enregistré dans le catalogue.`,
                '#c0392b'
            );
        } else {
            updateScanStatus('Analyse du code... gardez les barres nettes et horizontales.');
        }

        return false;
    }

    if (!scanOptions.knownCodes || scanOptions.knownCodes.length === 0) {
        const isEanCode = /^\d{8}$|^\d{13}$/.test(code);

        if (isEanCode && !isValidEanChecksum(code)) {
            updateScanStatus(`Lecture incertaine ${code}. Gardez le code bien horizontal.`, '#c0392b');
            return false;
        }
    }

    const detectionError = getDetectionError(result);
    const maxError = scanOptions.maxError;

    if (detectionError > maxError) {
        updateScanStatus('Lecture floue. Rapprochez le code et gardez-le horizontal.', '#c0392b');
        return false;
    }

    const count = countScanCandidate(code);
    const requiredReads = scanOptions.requiredReads;

    updateScanStatus(`Vérification du code ${code}... ${count}/${requiredReads}`);

    return count >= requiredReads;
}

function handleDetected(result) {
    if (!result || !result.codeResult || !result.codeResult.code) {
        return;
    }

    const code = normalizeScannedCode(result.codeResult.code);
    const now = Date.now();

    if (!shouldAcceptScannedCode(code, result)) {
        return;
    }
    
    // Éviter les doublons (même code dans les 2 secondes)
    if (code === lastCode && (now - lastTime) < 2000) {
        return;
    }
    
    lastCode = code;
    lastTime = now;
    
    console.log('Code-barre détecté:', code);
    
    if (onScanSuccess) {
        onScanSuccess(code);
    }
}

/**
 * Arrêter le scanner
 */
function stopScanner(resetState = false) {
    scannerSessionId++;

    if (Quagga) {
        if (quaggaHandlerAttached && typeof Quagga.offDetected === 'function') {
            Quagga.offDetected(handleDetected);
            quaggaHandlerAttached = false;
        }

        if (isScanning || scannerInitialized) {
            try {
                Quagga.stop();
                activeQuaggaSessionId = 0;
            } catch (error) {
                console.warn('Arrêt QuaggaJS incomplet:', error);
            }
        }
    }

    isScanning = false;

    if (resetState) {
        scannerInitialized = false;
        onScanSuccess = null;
        onScanError = null;
        scanCandidates.clear();
    }

    console.log('Scanner arrêté');
}

/**
 * Redémarrer le scanner
 */
function restartScanner() {
    if (Quagga && scannerInitialized && !isScanning) {
        if (!quaggaHandlerAttached) {
            Quagga.onDetected(handleDetected);
            quaggaHandlerAttached = true;
        }

        Quagga.start();
        isScanning = true;
        console.log('Scanner redémarré');
    }
}

/**
 * Obtenir le statut du scanner
 */
function getScannerStatus() {
    return {
        isScanning: isScanning,
        initialized: scannerInitialized
    };
}

// ==========================================
// Interface alternative avec ZXing (plus légère)
// ==========================================

/**
 * Scanner simple avec la caméra en utilisant l'API Barcode Detection
 * Plus légère que QuaggaJS, fonctionne sur Chrome/Android
 */
class SimpleBarcodeScanner {
    constructor(videoElement) {
        this.video = videoElement;
        this.stream = null;
        this.scanning = false;
        this.callback = null;
        this.errorCallback = null;
        this.detector = null;
        this.animationFrameId = null;
        this.consecutiveErrors = 0;
    }
    
    async start(callback, errorCallback) {
        this.callback = callback;
        this.errorCallback = errorCallback;
        
        try {
            if (!supportsBarcodeDetector()) {
                throw new Error('Scanner natif non supporté');
            }

            let formats = getNativeBarcodeFormats();

            if (typeof BarcodeDetector.getSupportedFormats === 'function') {
                const supportedFormats = await BarcodeDetector.getSupportedFormats();
                formats = formats.filter(format => supportedFormats.includes(format));

                if (formats.length === 0) {
                    throw new Error('Formats de code-barres non supportés');
                }
            }

            this.detector = new BarcodeDetector({ formats });

            // Demander l'accès à la caméra
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });
            
            // Configurer la vidéo
            this.video.srcObject = this.stream;
            await this.video.play();
            
            this.scanning = true;
            
            // Démarrer la détection
            this.scheduleDetection();
            
            return true;
        } catch (error) {
            console.error('Erreur caméra:', error);
            throw error;
        }
    }

    scheduleDetection() {
        if (!this.scanning || this.animationFrameId !== null) {
            return;
        }

        this.animationFrameId = requestAnimationFrame(() => {
            this.animationFrameId = null;
            this.detect();
        });
    }
    
    async detect() {
        if (!this.scanning || !this.detector) return;

        try {
            const barcodes = await this.detector.detect(this.video);
            this.consecutiveErrors = 0;

            if (barcodes.length > 0) {
                const code = normalizeScannedCode(barcodes[0].rawValue);
                const result = {
                    codeResult: {
                        code: code,
                        decodedCodes: []
                    }
                };

                if (!shouldAcceptScannedCode(code, result)) {
                    this.scheduleDetection();
                    return;
                }

                console.log("CODE:", code);
                if (typeof this.callback === 'function') {
                    this.callback(code);
                }

                return; // stop après détection
            }
        } catch (error) {
            this.consecutiveErrors++;
            console.error('Erreur de détection native:', error);

            if (this.consecutiveErrors >= 3) {
                const errorCallback = this.errorCallback;
                this.stop();

                if (typeof errorCallback === 'function') {
                    errorCallback(error);
                }
                return;
            }
        }

        this.scheduleDetection();
    }

    stop() {
        this.scanning = false;

        if (this.animationFrameId !== null) {
            cancelAnimationFrame(this.animationFrameId);
            this.animationFrameId = null;
        }
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        if (this.video) {
            this.video.srcObject = null;
        }

        this.detector = null;
        this.callback = null;
        this.errorCallback = null;
    }
}

// ==========================================
// Fonctions utilitaires pour l'intégration
// ==========================================

/**
 * Créer un élément vidéo pour le scanner
 * @param {string} containerId - ID du conteneur
 * @param {function} onClose - Callback appelé à la fermeture
 * @returns {HTMLElement} - Élément vidéo créé
 */
function createVideoElement(containerId, onClose) {
    const container = document.getElementById(containerId);
    
    if (!container) {
        throw new Error(`Conteneur ${containerId} non trouvé`);
    }

    container.innerHTML = '';
    
    // Créer la vidéo
    const video = document.createElement('video');
    video.id = 'scanner-video';
    video.className = 'scanner-video';
    video.setAttribute('playsinline', '');
    video.setAttribute('autoplay', '');
    video.muted = true;
    
    // Créer le conteneur
    const scannerDiv = document.createElement('div');
    scannerDiv.id = 'scanner-container';
    scannerDiv.className = 'scanner-panel';

    const header = createScannerHeader(onClose);
    const viewport = createScannerViewport('scanner-viewport');
    const cameraTarget = createScannerCameraTarget();
    const status = createScannerStatus('Pointez la caméra vers le code-barres');
    
    // Créer le bouton de fermeture
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Fermer';
    closeBtn.className = 'btn scanner-action';
    closeBtn.onclick = () => {
        if (typeof onClose === 'function') {
            onClose();
        } else {
            if (window.currentScanner) {
                window.currentScanner.stop();
            }
            scannerDiv.remove();
        }
    };
    
    cameraTarget.appendChild(video);
    viewport.appendChild(cameraTarget);
    viewport.appendChild(createScannerFrame());
    scannerDiv.appendChild(header);
    scannerDiv.appendChild(viewport);
    scannerDiv.appendChild(status);
    scannerDiv.appendChild(closeBtn);
    container.appendChild(scannerDiv);
    
    return video;
}

function closeScannerModal(modal) {
    if (window.currentScanner) {
        window.currentScanner.stop();
        window.currentScanner = null;
    }

    stopScanner(true);

    if (modal && modal.parentNode) {
        modal.remove();
    }

    document.body.classList.remove('scanner-open');
}

function showScannerMessage(container, message, color = '#666') {
    let messageElement = container.querySelector('.scanner-status') || document.getElementById('scanner-message');

    if (!messageElement) {
        messageElement = document.createElement('p');
        messageElement.id = 'scanner-message';
        container.prepend(messageElement);
    }

    messageElement.textContent = message;
    messageElement.style.color = color;
}

function createFallbackCloseButton(modal) {
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Fermer';
    closeBtn.className = 'btn scanner-action';
    closeBtn.onclick = () => closeScannerModal(modal);

    return closeBtn;
}

function createScannerHeader(onClose) {
    const header = document.createElement('div');
    header.className = 'scanner-header';

    const titleGroup = document.createElement('div');

    const title = document.createElement('h3');
    title.textContent = 'Scanner code-barres';

    const subtitle = document.createElement('p');
    subtitle.textContent = 'Placez le code dans le cadre';

    titleGroup.appendChild(title);
    titleGroup.appendChild(subtitle);

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'scanner-close';
    closeBtn.textContent = 'X';
    closeBtn.setAttribute('aria-label', 'Fermer le scanner');
    closeBtn.onclick = onClose;

    header.appendChild(titleGroup);
    header.appendChild(closeBtn);

    return header;
}

function createScannerViewport(id) {
    const viewport = document.createElement('div');
    viewport.id = id;
    viewport.className = 'scanner-viewport';

    return viewport;
}

function createScannerCameraTarget() {
    const cameraTarget = document.createElement('div');
    cameraTarget.id = 'scanner-camera';
    cameraTarget.className = 'scanner-camera';

    return cameraTarget;
}

function createScannerFrame() {
    const frame = document.createElement('div');
    frame.className = 'scanner-frame';
    frame.setAttribute('aria-hidden', 'true');

    const line = document.createElement('span');
    line.className = 'scanner-line';
    frame.appendChild(line);

    return frame;
}

function createScannerStatus(text) {
    const status = document.createElement('p');
    status.id = 'scanner-message';
    status.className = 'scanner-status';
    status.textContent = text;

    return status;
}

function startQuaggaScanner(modal, container, onScan) {
    container.innerHTML = '';

    const scannerDiv = document.createElement('div');
    scannerDiv.id = 'scanner-container';
    scannerDiv.className = 'scanner-panel';

    const viewport = createScannerViewport('scanner-viewport');
    viewport.appendChild(createScannerCameraTarget());
    viewport.appendChild(createScannerFrame());

    scannerDiv.appendChild(createScannerHeader(() => closeScannerModal(modal)));
    scannerDiv.appendChild(viewport);
    scannerDiv.appendChild(createScannerStatus('Pointez la caméra vers le code-barres'));
    scannerDiv.appendChild(createFallbackCloseButton(modal));
    container.appendChild(scannerDiv);

    initScanner('#scanner-camera', (code) => {
        onScan(code);
        closeScannerModal(modal);
    }, (error) => {
        console.error('Erreur scanner:', error);
        showScannerMessage(
            scannerDiv,
            getCameraErrorMessage(error),
            '#e74c3c'
        );
    });
}

/**
 * Ouvrir le scanner dans un modal
 * @param {function} onScan - Callback appelé avec le code-barre
 */
function openScannerModal(onScan, options = {}) {
    if (typeof onScan !== 'function') {
        throw new TypeError('Le callback du scanner doit être une fonction.');
    }

    const requiredReads = Number(options.requiredReads);
    const maxError = Number(options.maxError);

    scanOptions = {
        knownCodes: Array.isArray(options.knownCodes)
            ? options.knownCodes.map(normalizeScannedCode).filter(Boolean)
            : [],
        requiredReads: Number.isFinite(requiredReads) && requiredReads >= 1
            ? Math.floor(requiredReads)
            : 3,
        maxError: Number.isFinite(maxError) && maxError >= 0
            ? maxError
            : 0.28
    };

    // Créer le modal
    const modal = document.createElement('div');
    modal.id = 'scanner-modal';
    modal.className = 'scanner-modal';
    
    const container = document.createElement('div');
    container.id = 'scanner-content';
    container.className = 'scanner-content';
    
    modal.appendChild(container);
    document.body.appendChild(modal);
    document.body.classList.add('scanner-open');

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeScannerModal(modal);
        }
    });

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showScannerMessage(
            container,
            "Caméra non disponible. Utilisez localhost/HTTPS et autorisez l'accès à la caméra.",
            '#e74c3c'
        );
        container.appendChild(createFallbackCloseButton(modal));
        return;
    }

    if (!supportsBarcodeDetector()) {
        startQuaggaScanner(modal, container, onScan);
        return;
    }

    // Créer la vidéo
    const video = createVideoElement('scanner-content', () => closeScannerModal(modal));
    
    // Créer le scanner
    const scanner = new SimpleBarcodeScanner(video);
    window.currentScanner = scanner;

    let fallbackStarted = false;
    const fallbackToQuagga = (error) => {
        if (fallbackStarted || !modal.isConnected || window.currentScanner !== scanner) {
            return;
        }

        fallbackStarted = true;
        console.warn('Scanner natif indisponible, bascule vers QuaggaJS:', error);
        scanner.stop();
        window.currentScanner = null;
        startQuaggaScanner(modal, container, onScan);
    };
    
    scanner.start((code) => {
        onScan(code);
        closeScannerModal(modal);
    }, fallbackToQuagga).catch(fallbackToQuagga);
}

// Export pour utilisation globale
window.Scanner = {
    initScanner,
    stopScanner,
    restartScanner,
    getScannerStatus,
    SimpleBarcodeScanner,
    openScannerModal
};
