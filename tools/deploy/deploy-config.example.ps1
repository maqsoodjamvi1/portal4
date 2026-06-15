# Copy this file to deploy-config.local.ps1 and fill in your server details.
# deploy-config.local.ps1 is ignored by git — do not commit credentials.

$DeployConfig = @{
    # Local project folder (this repo)
    LocalPath = 'C:\xhamp\htdocs\school-management-system'

    # Remote folder on the server (SFTP path)
    # portal4.timesoftsol.com uses:
    RemotePath = '/var/www/portal4/html'
    # Other installs may use: '/var/www/html/school-management-system'

    Host = 'your-server.com'
    Port = 22
    User = 'your-username'

    # Leave blank to be prompted each run (recommended).
    Password = ''

    # Or use a private key instead of a password:
    # PrivateKeyPath = 'C:\Users\You\.ssh\id_rsa.ppk'

    # Optional: pin the server host key after first connect in WinSCP (Session > Advanced > SSH > Authentication)
    # HostKey = 'ssh-ed25519 255 xx:xx:...'

    # WinSCP command-line tool (WinSCP.com)
    WinScpPath = 'C:\Program Files (x86)\WinSCP\WinSCP\WinSCP.com'

    # Upload only files changed locally (by time). Use 'checksum' for stricter matching (slower).
    SyncCriteria = 'time'
}
