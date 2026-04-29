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

// Callback appelé quand un code-barre est détecté
let onScanSuccess = null;
let onScanError = null;

/**
 * Charger la bibliothèque QuaggaJS dynamiquement
 */
function loadQuaggaJS() {
    return new Promise((resolve, reject) => {
        if (window.Quagga) {
            resolve(window.Quagga);
            return;
        }
        
        // Créer le script
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js';
        script.async = true;
        
        script.onload = () => {
            resolve(window.Quagga);
        };
        
        script.onerror = () => {
            reject(new Error('Échec du chargement de QuaggaJS'));
        };
        
        document.head.appendChild(script);
    });
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
    
    onScanSuccess = successCallback;
    onScanError = errorCallback;
    
    try {
        // Charger QuaggaJS
        await loadQuaggaJS();
        
        // Configurer Quagga
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector(targetElement),
                constraints: {
                    width: { min: 640 },
                    height: { min: 480 },
                    facingMode: "environment" // Utiliser la caméra arrière si disponible
                }
            },
            decoder: {
                readers: [
                    "ean_reader",
                    "ean_8_reader",
                    "code_128_reader",
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader"
                ]
            },
            locate: true,
            locator: {
                patchSize: "medium",
                halfSample: true
            }
        }, function(err) {
            if (err) {
                console.error('Erreur QuaggaJS:', err);
                if (onScanError) onScanError(err);
                return;
            }
            
            Quagga.start();
            isScanning = true;
            scannerInitialized = true;
            console.log('Scanner démarré');
        });
        
        // Gestionnaire de détection
        Quagga.onDetected(handleDetected);
        
    } catch (error) {
        console.error('Erreur initialization:', error);
        if (onScanError) onScanError(error);
    }
}

/**
 * Gérer la détection d'un code-barre
 */
let lastCode = null;
let lastTime = 0;

function handleDetected(result) {
    const code = result.codeResult.code;
    const now = Date.now();
    
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
function stopScanner() {
    if (Quagga && isScanning) {
        Quagga.stop();
        isScanning = false;
        console.log('Scanner arrêté');
    }
}

/**
 * Redémarrer le scanner
 */
function restartScanner() {
    if (Quagga && !isScanning) {
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
    }
    
    async start(callback) {
        this.callback = callback;
        
        try {
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
            this.detect();
            
            return true;
        } catch (error) {
            console.error('Erreur caméra:', error);
            throw error;
        }
    }
    
    async detect() {
    if (!this.scanning) return;

    if (!('BarcodeDetector' in window)) {
        alert("Scanner non supporté sur ce navigateur");
        return;
    }

    const barcodeDetector = new BarcodeDetector({
        formats: ['ean_13', 'ean_8', 'code_128']
    });

    try {
        const barcodes = await barcodeDetector.detect(this.video);

        if (barcodes.length > 0) {
            console.log("CODE:", barcodes[0].rawValue);

            this.callback(barcodes[0].rawValue);

            return; // stop après détection
        }
    } catch (err) {
        console.error(err);
    }

    requestAnimationFrame(() => this.detect());
    }
    stop() {
        this.scanning = false;
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        if (this.video) {
            this.video.srcObject = null;
        }
    }
}

// ==========================================
// Fonctions utilitaires pour l'intégration
// ==========================================

/**
 * Créer un élément vidéo pour le scanner
 * @param {string} containerId - ID du conteneur
 * @returns {HTMLElement} - Élément vidéo créé
 */
function createVideoElement(containerId) {
    const container = document.getElementById(containerId);
    
    if (!container) {
        throw new Error(`Conteneur ${containerId} non trouvé`);
    }
    
    // Créer la vidéo
    const video = document.createElement('video');
    video.id = 'scanner-video';
    video.style.width = '100%';
    video.style.maxWidth = '500px';
    video.style.borderRadius = '8px';
    video.setAttribute('playsinline', '');
    video.setAttribute('autoplay', '');
    video.muted = true;
    
    // Créer le conteneur
    const scannerDiv = document.createElement('div');
    scannerDiv.id = 'scanner-container';
    scannerDiv.style.textAlign = 'center';
    
    // Créer le message d'instruction
    const instruction = document.createElement('p');
    instruction.textContent = 'Pointez la caméra vers le code-barres';
    instruction.style.margin = '10px 0';
    instruction.style.color = '#666';
    
    // Créer le bouton de fermeture
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Fermer';
    closeBtn.className = 'btn';
    closeBtn.style.marginTop = '10px';
    closeBtn.onclick = () => {
        if (window.currentScanner) {
            window.currentScanner.stop();
        }
        scannerDiv.remove();
    };
    
    scannerDiv.appendChild(instruction);
    scannerDiv.appendChild(video);
    scannerDiv.appendChild(closeBtn);
    container.appendChild(scannerDiv);
    
    return video;
}

/**
 * Ouvrir le scanner dans un modal
 * @param {function} onScan - Callback appelé avec le code-barre
 */
function openScannerModal(onScan) {
    // Créer le modal
    const modal = document.createElement('div');
    modal.id = 'scanner-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    const container = document.createElement('div');
    container.id = 'scanner-content';
    container.style.cssText = `
        width: 90%;
        max-width: 500px;
        background: white;
        padding: 20px;
        border-radius: 10px;
    `;
    
    modal.appendChild(container);
    document.body.appendChild(modal);
    
    // Créer la vidéo
    const video = createVideoElement('scanner-content');
    
    // Créer le scanner
    const scanner = new SimpleBarcodeScanner(video);
    window.currentScanner = scanner;
    
    scanner.start((code) => {
        onScan(code);
        scanner.stop();
        modal.remove();
    }).catch((error) => {
        alert('Erreur: ' + error.message);
        modal.remove();
    });
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


