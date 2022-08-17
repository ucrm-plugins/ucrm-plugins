
module UISP
    # Attempt to automatically determine the UCRM version based on the UISP version provided.
    # WATCH: Currently the UCRM version is ALWAYS exactly 2 major versions ahead.
    def UISP.getUcrmVersion (uispVersion)
        uispVersion.gsub(/^(\d+)/) { |capture| (capture.to_i + 2).to_s }
    end
end
