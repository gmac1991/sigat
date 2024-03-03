import notificacao from './controller/notificacaoEmail.js';
import cron from 'node-cron';
import fs from 'fs';

function atualizaNotificacao() {
    notificacao.atualizarNotificacao().then((data) => {
        const horas = new Date();

        const logMessage = {
            data: horas,
            menssagem: data,
            erro: false
        }
        const arquivo = './logs/logEvento.json';
        fs.readFile(arquivo, 'utf8', (err, conteudoAntigo) => {
            if (err) {
                console.error('Ocorreu um erro ao ler o arquivo JSON:', err);
                return;
            }

            // Parse do conteúdo antigo para um objeto JavaScript
            let conteudoObjeto;
            try {
                conteudoObjeto = [JSON.parse(conteudoAntigo)];
            } catch (parseError) {
                console.error('Ocorreu um erro ao fazer o parse do conteúdo JSON existente:', parseError);
                return;
            }
        
            // Concatenar o novo conteúdo com o conteúdo existente
            conteudoObjeto.push(logMessage);
            const novoConteudo = JSON.stringify(conteudoObjeto);
        
            // Escrever o conteúdo atualizado de volta no arquivo
            fs.writeFile(arquivo, novoConteudo, 'utf8', (err) => {
                if (err) {
                    console.error('Ocorreu um erro ao gravar o arquivo JSON:', err);
                    return;
                }
                console.log('O novo conteúdo foi concatenado e gravado com sucesso no arquivo JSON.');
            });
        });
    });
}

export default atualizaNotificacao();


// Função a ser executada a cada 15 minutos, das 7h00 às 20h00 nos dias de semana
/* const weekdaySchedule = () => {
    atualizaNotificacao();
}; */

//export default cron.schedule('*/1 1-23 * * 1-5', weekdaySchedule); // Every 5 minutes from 7:00 to 20:00, Monday to Friday