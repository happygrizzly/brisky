<?php 

    namespace App\Bin\Composer;

    class ComposerInstallHook {

        public static function install() {

            @mkdir('src/app/var/cache', 0777);
            @mkdir('src/app/var/log', 0777);

            chmod('src/bin/console', 0755);
            chmod('uploads', 4664);
            
        }

    }

?>