<style>
@keyframes vipBorderFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes vipShimmerSweep {
    0% { transform: translateX(-150%) skewX(-20deg); }
    100% { transform: translateX(300%) skewX(-20deg); }
}
@keyframes vipPulseGlow {
    0%, 100% { box-shadow: 0 0 16px rgba(245, 158, 11, 0.35), 0 0 32px rgba(234, 88, 12, 0.2); }
    50% { box-shadow: 0 0 28px rgba(245, 158, 11, 0.65), 0 0 48px rgba(234, 88, 12, 0.4); }
}
.vip-download-card {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    padding: 2.5px !important;
    border-radius: 18px !important;
    text-decoration: none !important;
    background: linear-gradient(90deg, #F59E0B, #EA580C, #FBBF24, #D97706, #F59E0B) !important;
    background-size: 300% 300% !important;
    animation: vipBorderFlow 4s ease infinite, vipPulseGlow 3s infinite ease-in-out !important;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease !important;
    overflow: hidden !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
}
.vip-download-card:hover {
    transform: translateY(-2px) scale(1.008) !important;
    box-shadow: 0 14px 40px rgba(245, 158, 11, 0.55) !important;
}
.vip-download-card-inner {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 14px !important;
    background: #14110E !important;
    padding: 16px 22px !important;
    border-radius: 15.5px !important;
    overflow: hidden !important;
    transition: background-color 0.2s ease !important;
}
@media (min-width: 640px) {
    .vip-download-card-inner {
        flex-direction: row !important;
    }
}
.vip-download-card:hover .vip-download-card-inner {
    background: #1C1611 !important;
}
.vip-shimmer-ray {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 40% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.16), transparent) !important;
    pointer-events: none !important;
    animation: vipShimmerSweep 2.8s infinite ease-in-out !important;
}
.vip-content-left {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    text-align: center !important;
    z-index: 2 !important;
}
@media (min-width: 640px) {
    .vip-content-left {
        text-align: left !important;
    }
}
.vip-icon-wrapper {
    position: relative !important;
    width: 48px !important;
    height: 48px !important;
    border-radius: 14px !important;
    background: linear-gradient(135deg, #F59E0B, #EA580C) !important;
    padding: 2px !important;
    flex-shrink: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.45) !important;
}
.vip-icon-inner {
    width: 100% !important;
    height: 100% !important;
    background: #18130E !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.vip-icon-inner svg {
    width: 26px !important;
    height: 26px !important;
    color: #FBBF24 !important;
    filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.7)) !important;
    transition: transform 0.2s ease !important;
}
.vip-download-card:hover .vip-icon-inner svg {
    transform: scale(1.15) rotate(-6deg) !important;
}
.vip-title-row {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}
@media (min-width: 640px) {
    .vip-title-row {
        justify-content: flex-start !important;
    }
}
.vip-title-text {
    font-family: var(--font-heading, inherit) !important;
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    letter-spacing: -0.015em !important;
    color: #FFFFFF !important;
    line-height: 1.25 !important;
    transition: color 0.15s ease !important;
}
.vip-download-card:hover .vip-title-text {
    color: #FDE68A !important;
}
.vip-badge-pill {
    display: inline-flex !important;
    align-items: center !important;
    padding: 3px 10px !important;
    border-radius: 9999px !important;
    font-size: 10px !important;
    font-weight: 900 !important;
    font-family: var(--font-mono, monospace) !important;
    letter-spacing: 0.06em !important;
    text-transform: uppercase !important;
    background: linear-gradient(135deg, #F59E0B, #EA580C) !important;
    color: #000000 !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4) !important;
    line-height: 1.2 !important;
}
.vip-subtext {
    font-size: 12px !important;
    color: #A89E90 !important;
    margin-top: 4px !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
}
.vip-action-cta {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 10px 20px !important;
    border-radius: 13px !important;
    background: linear-gradient(135deg, #F59E0B, #EA580C) !important;
    color: #000000 !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4) !important;
    transition: all 0.2s ease !important;
    z-index: 2 !important;
    flex-shrink: 0 !important;
    text-decoration: none !important;
}
.vip-action-cta span {
    color: #000000 !important;
    font-weight: 800 !important;
}
.vip-action-cta svg {
    width: 16px !important;
    height: 16px !important;
    stroke: #000000 !important;
    stroke-width: 2.8 !important;
    transition: transform 0.2s ease !important;
}
.vip-download-card:hover .vip-action-cta {
    background: linear-gradient(135deg, #FBBF24, #F97316) !important;
    box-shadow: 0 6px 24px rgba(245, 158, 11, 0.6) !important;
}
.vip-download-card:hover .vip-action-cta svg {
    transform: translateX(4px) !important;
}
</style>
