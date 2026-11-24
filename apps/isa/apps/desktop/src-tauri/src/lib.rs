use serde::Serialize;

#[derive(Clone, Serialize)]
#[serde(rename_all = "camelCase")]
struct AppStatus {
    name: &'static str,
    phase: &'static str,
    storage: &'static str,
    network_required: bool,
}

#[tauri::command]
fn get_app_status() -> AppStatus {
    AppStatus {
        name: "ISA — English Tutor",
        phase: "foundation",
        storage: "not configured",
        network_required: false,
    }
}

pub fn run() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![get_app_status])
        .run(tauri::generate_context!())
        .expect("error while running ISA desktop application");
}

#[cfg(test)]
mod tests {
    use super::get_app_status;

    #[test]
    fn reports_foundation_as_offline_first() {
        let status = get_app_status();
        assert_eq!(status.phase, "foundation");
        assert!(!status.network_required);
    }
}
