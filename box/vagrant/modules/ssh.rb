
module SSH


    def SSH.copyConfig (host, ip = nil)

        sshPath = File.expand_path("~/.ssh")
        configFile = "#{sshPath}/vagrant_#{host}.cfg"
        hostRegEx = /^Host (.*)$/

        SSH.deleteConfig(host)

        sshInfo = `vagrant ssh-config`
        if config = sshInfo.match(hostRegEx)
            sshInfo = sshInfo.gsub(/^Host (.*)$/, "Host #{host}")
            sshInfo = sshInfo.gsub(/^  HostName (.*)$/, "  HostName " + (ip ? ip : host))
            sshInfo = sshInfo.gsub(/^  Port (.*)$/, "  Port 22")

            File.open(configFile, "w") { |file| file.puts sshInfo }
        else
            # Something went wrong!
        end

    end

    def SSH.deleteConfig (host = "uisp-dev")

        sshPath = File.expand_path("~/.ssh")
        configFile = "#{sshPath}/vagrant_#{host}.cfg"

        # Delete any previous config file.
        if File.exist?(configFile)
            File.delete(configFile)
        end

    end

    def SSH.updateScript(path, host = "uisp-dev")

        scriptFile = "#{path}/vssh.bat"

        if File.exist?(scriptFile)
            contents = File.read(scriptFile)
            contents = contents.gsub(/^SET SSH_HOST=(.*)$/, "SET SSH_HOST=#{host}")
            File.open(scriptFile, "w") { |file| file.puts contents }
        end

    end



    def SSH.copyPrivateKey (hostname)
        keyPath = File.expand_path("~/.ssh")
        keyFile = "#{keyPath}/#{hostname}_private_key"
        sshInfo = `vagrant ssh-config`
        configFile = "#{keyPath}/config_#{hostname}"

        if config = /^Host (.*)$/.match(`vagrant ssh-config`)
            File.open(configFile, "w") { |file| file.puts config }


        end

        # Delete any previous key file.
        if File.exist?(keyFile)
            File.delete(keyFile)
        end

        if config = /^\s*IdentityFile\s*(?<IdentityFile>.*)$/.match(`vagrant ssh-config`)
            FileUtils.cp(config["IdentityFile"], keyPath)
            filename = File.basename(config["IdentityFile"])
            File.rename("#{keyPath}/#{filename}", "#{keyPath}/#{keyFile}")
        end



    end

    def SSH.deletePrivateKey (hostname)
        keyPath = File.expand_path("~/.ssh")
        keyFile = "#{hostname}_private_key"

        if File.exist?("#{keyPath}/#{keyFile}")
            File.delete("#{keyPath}/#{keyFile}")
        end
    end

    def SSH.updateKnownHosts (host, delete = false)

        hostsFile = File.expand_path("~/.ssh/known_hosts")
        hostRegEx = /^(?<hosts>[^\s]*#{Regexp.escape(host)},?[^\s]*)\s+(?<type>ecdsa-sha2-nistp256)\s+(?<key>.*=)$/
        scanRegEx = /^.*(?<type>ecdsa-sha2-nistp256)\s+(?<key>.*=)$/

        if File.exist?(hostsFile)
            #ssh-keyscan -t ecdsa -H uisp-dev

            contents = File.read(hostsFile)

            if delete
                newContents = contents.gsub(hostRegEx, "")
            else
                if scanInfo = scanRegEx.match(`ssh-keyscan -t ecdsa -H #{host}`)
                    key = scanInfo["key"]
                    newContents = text.gsub(hostRegEx, "#{host} ecdsa-sha2-nistp256 #{key}")
                end
            end

            if (newContents != contents)
                File.open(hostsFile, "w") { |file| file.puts newContents }
                #puts newContents
            end

        end

    end

end
