
module SSH
    @@machine = {}

    def self.setMachine(value)
        @@machine = value
    end

    def self.getMachine()
        @@machine
    end

    def SSH.log(message)
        name = @@machine ? @@machine.name : "default"
        puts "    #{name}: [VSSH] #{message}"
    end

    def SSH.sshPath()
        File.expand_path("~/.ssh")
    end

    def SSH.sshInfo()
        SSH.log("Querying 'ssh-config'...")
        `vagrant ssh-config`
    end

    def SSH.updateConfig(host = nil, alts = nil, name = nil, user = nil, port = nil) #, path = "~/")
        file = "#{SSH.sshPath()}/config"
        info = SSH.sshInfo().gsub(/([\r?\n]){2,}/, '\1')

        # Modify the config as specified...
        if info.match(/^Host (.*)$/)
            host ||= info.match(/^Host (.*)$/).captures[0]
            alts ||= ""
            name ||= info.match(/^  HostName (.*)$/).captures[0]
            user ||= info.match(/^  User (.*)$/).captures[0]
            port ||= info.match(/^  Port (.*)$/).captures[0]

            info = info
                .gsub(/^Host (.*)$/, "Host #{host} #{alts}")
                .gsub(/^  HostName (.*)$/, "  HostName #{name}")
                .gsub(/^  User (.*)$/, "  User #{user}")
                .gsub(/^  Port (.*)$/, "  Port #{port}")

            #info += "  RequestTTY force"
            #info += "  RemoteCommand cd #{path} && bash -l"
        end

        #puts host, name, user, port

        contents = ""
        found = false

        # IF an SSH config file already exists...
        if File.exist?(file)
            # ...THEN read the current config's contents.
            contents = File.read(file)

            # Loop through each config block...
            contents = contents.gsub(/(?<=^)(\S.*?)(?=^\S|\Z)/ms) do |m|
                if m.match(/^Host #{host}/)
                    #puts "    #{SSH.getMachine().name}: [SSH] Updating '#{host}'..."
                    SSH.log("Updating '#{host}'...")
                    found = true
                    info
                else
                    m
                end
            end

            if not found
                SSH.log("Adding '#{host}'...")
                contents += info
            end
        else
            # ...OTHERWISE, create one!
            SSH.log("Creating 'config' file...")
            SSH.log("Adding '#{host}'...")
            contents = info
        end

        File.open(file, "wb") { |f| f.puts contents }
    end

    def SSH.deleteConfig (host)
        file = "#{SSH.sshPath()}/config"

        contents = ""
        found = false

        # IF an SSH config file already exists...
        if File.exist?(file)
            # ...THEN read the current config's contents.
            contents = File.read(file)

            # Loop through each config block...
            contents = contents.gsub(/(?<=^)(\S.*?)(?=^\S|\Z)/ms) do |m|
                if m.match(/^Host #{host}/)
                    SSH.log("Deleting '#{host}'...")
                    found = true
                    ""
                else
                    m
                end
            end
        else
            # ...OTHERWISE, nothing to do!
        end

        SSH.log("Cleaning up config file...")
        contents = contents.gsub(/([\r?\n]){3,}/, '\1\1')

        if contents.strip.empty?
            if File.exists?(file)
                SSH.log("Deleting empty config file...")
                File.delete(file)
            else
                SSH.log("Ignoring empty config...")
            end
        else
            SSH.log("Saving config changes...")
            File.open(file, "wb") { |f| f.puts contents }
        end
    end

    def SSH.updateScript(file, key, value)
        if File.exist?(file)
            SSH.log("Updating 'vssh'...")
            contents = File.read(file)
            contents = contents.gsub(/^#{key}=(.*)$/, "#{key}=#{value}")
            File.open(file, "wb") { |f| f.puts contents }
        end
    end

end
